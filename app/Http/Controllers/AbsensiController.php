<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\AbsensiHarian; // Import Model Harian
use App\Models\Karyawan;
use App\Models\Kalender;
use Carbon\Carbon;   
use Carbon\CarbonPeriod;

use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        // ---------------------------------------------------------------------
        // CEK: JIKA FILTER BELUM DIPILIH, JANGAN TAMPILKAN APA-APA
        // ---------------------------------------------------------------------
        if (!$bulan || !$tahun) {
            // Kembalikan view dengan data kosong
            return view('absensi.index', [
                'absensis' => collect([]), // Collection kosong agar tabel tidak error
                'bulan' => null,
                'tahun' => null
            ]);
        }

        // Jika filter sudah ada, baru jalankan query
        $query = Absensi::query();

        if ($bulan) {
            $query->where('bulan', $bulan);
        }

        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        $absensis = $query
            ->orderBy('departemen') 
            ->orderBy('nama')
            ->get();

        return view('absensi.index', compact('absensis'));
    }

    public function index1(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $query = Absensi::query();

        if ($bulan) {
            $query->where('bulan', $bulan);
        }

        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        $absensis = $query
            ->orderBy('departemen') 
            ->orderBy('nama')
            ->get();

        return view('absensi.index', compact('absensis'));
    }

    public function rekapitulasiFix(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        $bulan = $request->bulan;
        $tahun = $request->tahun;

        DB::beginTransaction();
        try {
            // =========================================================================
            // LANGKAH 1: TENTUKAN "TARGET KEHADIRAN" (DIBUTUHKAN)
            // =========================================================================
            // Kita hitung ada berapa tanggal unik di tabel harian pada bulan & tahun tsb.
            // Contoh: Jika di harian cuma ada tgl 1, 2, 3 ... 10, maka hasilnya 10.
            // Ini menjawab keluhan Anda kenapa muncul 12 padahal data cuma 10.
            $totalHariKerjaAktual = AbsensiHarian::whereYear('tanggal', $tahun)
                                ->whereMonth('tanggal', $bulan)
                                ->distinct('tanggal') // Hanya hitung tanggal unik
                                ->count('tanggal');

            // Jika belum ada data harian sama sekali, stop proses.
            if ($totalHariKerjaAktual == 0) {
                return back()->with('error', 'Data Absensi Harian kosong. Harap input/import data harian terlebih dahulu.');
            }

            // =========================================================================
            // LANGKAH 2: LOOPING SETIAP KARYAWAN
            // =========================================================================
            $karyawans = Karyawan::all();

            foreach ($karyawans as $karyawan) {

                // Ambil Data Harian milik karyawan ini saja
                $harian = AbsensiHarian::where('id_karyawan', $karyawan->id_karyawan)
                            ->whereYear('tanggal', $tahun)
                            ->whereMonth('tanggal', $bulan)
                            ->get();

                // PERBAIKAN: Jika karyawan ini tidak punya data harian sama sekali,
                // lewati (jangan buat rekap untuk dia), sesuai permintaan Anda.
                if ($harian->isEmpty()) {
                    continue;
                }

                // =====================================================================
                // LANGKAH 3: HITUNG AGREGAT (SUM & COUNT)
                // =====================================================================
                
                // Hitung Status
                $hadir = $harian->where('status_kehadiran', 'Hadir')->count();
                $sakit = $harian->where('status_kehadiran', 'Sakit')->count();
                $izin  = $harian->where('status_kehadiran', 'Izin')->count();
                
                // Alpha diambil dari status 'Tidak Hadir' yang sudah digenerate di Harian
                $alpha = $harian->where('status_kehadiran', 'Tidak Hadir')->count();

                // Hitung Keterlambatan
                $totalTerlambatMenit = $harian->sum('terlambat_menit');
                $jumlahTerlambat     = $harian->where('terlambat_menit', '>', 0)->count();
                
                // Hitung Pulang Cepat
                $totalPulangCepatMenit = $harian->sum('pulang_cepat_menit');
                $jumlahPulangCepat     = $harian->where('pulang_cepat_menit', '>', 0)->count();

                // =====================================================================
                // LANGKAH 4: SIMPAN KE TABEL REKAP (ABSENSI)
                // =====================================================================
                Absensi::updateOrCreate(
                    [
                        // Kunci Pencarian (Where)
                        'id_karyawan' => $karyawan->id_karyawan,
                        'bulan'       => $bulan,
                        'tahun'       => $tahun,
                    ],
                    [
                        // Data yang diupdate/insert
                        'nama'                 => $karyawan->nama,
                        'departemen'           => $karyawan->departemen,
                        
                        // Target diambil dari hitungan unik Langkah 1
                        'dibutuhkan'           => $totalHariKerjaAktual, 
                        'target_kehadiran'     => $totalHariKerjaAktual,
                        
                        // Realisasi = Jumlah Hadir
                        'kenyataan'            => $hadir,          
                        'realisasi_kehadiran'  => $hadir,
                        
                        // Detail Status
                        'hadir'                => $hadir,
                        'sakit'                => $sakit,
                        'izin'                 => $izin,
                        'alpha'                => $alpha,
                        
                        // Detail Waktu
                        'keterlambatan_jumlah' => $jumlahTerlambat,
                        'keterlambatan_menit'  => $totalTerlambatMenit,
                        'keluar_awal_jumlah'   => $jumlahPulangCepat,
                        'keluar_awal_menit'    => $totalPulangCepatMenit,
                    ]
                );
            }

            DB::commit();
            return redirect()->route('absensi.index', ['bulan' => $bulan, 'tahun' => $tahun])
                ->with('success', "Rekap berhasil! Target kerja bulan ini terdeteksi: $totalHariKerjaAktual hari.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal rekap: ' . $e->getMessage());
        }
    }

   public function cetak(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        if (!$bulan || !$tahun) {
            return redirect()->route('absensi.index')->with('error', 'Silakan pilih bulan dan tahun terlebih dahulu.');
        }

        // --- UPDATE QUERY MENGGUNAKAN JOIN ---
        // Kita ambil semua data absensi, plus kolom 'jabatan' dari tabel karyawans
        $absensis = Absensi::select('absensis.*', 'karyawans.jabatan as jabatan_asli')
            ->leftJoin('karyawans', 'absensis.id_karyawan', '=', 'karyawans.id_karyawan')
            ->where('absensis.bulan', $bulan)
            ->where('absensis.tahun', $tahun)
            ->orderBy('karyawans.jabatan', 'asc') // Urutkan berdasarkan jabatan
            ->orderBy('absensis.nama', 'asc')
            ->get();

        $namaBulan = Carbon::create()->month((int)$bulan)->locale('id')->isoFormat('MMMM');

        return view('absensi.cetak', compact('absensis', 'bulan', 'tahun', 'namaBulan'));
    }

    public function rekapitulasiFixGagal(Request $request)
    {
        $request->validate([
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $today = Carbon::today();

        // --- 1. Tentukan akhir perhitungan hari ---
        // Jika bulan berjalan, hitung sampai hari ini saja. Jika bulan lalu, full sebulan.
        if ($bulan == $today->month && $tahun == $today->year) {
            $totalHariBulanIni = $today->day; 
        } else {
            $totalHariBulanIni = Carbon::create($tahun, $bulan)->daysInMonth;
        }

        // --- 2. Ambil semua tanggal libur dari Kalender ---
        $listLibur = [];
        $events = Kalender::whereYear('waktu_mulai', $tahun)
                        ->whereMonth('waktu_mulai', $bulan)
                        ->get();

        foreach ($events as $event) {
            $end = $event->waktu_berakhir ?? $event->waktu_mulai;
            $period = CarbonPeriod::create($event->waktu_mulai, $end);

            foreach ($period as $date) {
                $listLibur[] = $date->format('Y-m-d');
            }
        }

        $karyawans = Karyawan::all();

        DB::beginTransaction();
        try {

            foreach ($karyawans as $karyawan) {

                // --- 3. Ambil Absen Harian (Collection Mentah) ---
                $harianCollection = AbsensiHarian::where('id_karyawan', $karyawan->id_karyawan)
                                        ->whereYear('tanggal', $tahun)
                                        ->whereMonth('tanggal', $bulan)
                                        ->get();

                // ==================================================================
                // PERBAIKAN: Cek apakah ada data harian?
                // Jika kosong, lewati karyawan ini agar tidak terhitung Alpha sebulan penuh
                // ==================================================================
                if ($harianCollection->isEmpty()) {
                    continue; 
                }

                // Jika ada data, ubah jadi KeyBy tanggal untuk kemudahan mapping
                $harian = $harianCollection->keyBy(fn($item) => Carbon::parse($item->tanggal)->format('Y-m-d'));

                // Variabel hitungan
                $hadir = $sakit = $izin = $alpha = 0;
                $totalTerlambat = $jumlahTerlambat = 0;
                $totalPulangCepat = $jumlahPulangCepat = 0;

                // --- 4. Loop Harian (Tgl 1 s/d Akhir Bulan/Hari Ini) ---
                for ($day = 1; $day <= $totalHariBulanIni; $day++) {

                    $dateObj = Carbon::create($tahun, $bulan, $day);
                    $dateStr = $dateObj->format('Y-m-d');

                    $isSunday = $dateObj->isSunday();
                    $isLiburKalender = in_array($dateStr, $listLibur);

                    if (isset($harian[$dateStr])) {
                        // Jika ada data absen di tanggal ini
                        $d = $harian[$dateStr];

                        if ($d->status_kehadiran == 'Hadir') $hadir++;
                        if ($d->status_kehadiran == 'Sakit') $sakit++;
                        if ($d->status_kehadiran == 'Izin')  $izin++;
                        if ($d->status_kehadiran == 'Tidak Hadir') $alpha++;

                        // Hitung Keterlambatan
                        if ($d->terlambat_menit > 0) {
                            $jumlahTerlambat++;
                            $totalTerlambat += $d->terlambat_menit;
                        }

                        // Hitung Pulang Cepat
                        if ($d->pulang_cepat_menit > 0) {
                            $jumlahPulangCepat++;
                            $totalPulangCepat += $d->pulang_cepat_menit;
                        }

                    } else {
                        // Jika TIDAK ADA data absen di tanggal ini (Kosong)
                        // Cek apakah hari kerja? (Bukan Libur Kalender & Bukan Minggu)
                        if (!$isLiburKalender && !$isSunday) {
                            $alpha++; // Hitung sebagai Alpha otomatis
                        }
                    }
                }

                // --- 5. Simpan ke Database ---
                
                // Pengecekan ekstra: Pastikan tidak menyimpan data kosong murni
                if ($hadir == 0 && $sakit == 0 && $izin == 0 && $alpha == 0) {
                    continue;
                }

                Absensi::updateOrCreate(
                    [
                        'id_karyawan' => $karyawan->id_karyawan,
                        'bulan'       => $bulan,
                        'tahun'       => $tahun,
                    ],
                    [
                        'nama'                 => $karyawan->nama,
                        'departemen'           => $karyawan->departemen,
                        'hadir'                => $hadir,
                        'sakit'                => $sakit,
                        'izin'                 => $izin,
                        'alpha'                => $alpha,
                        'keterlambatan_jumlah' => $jumlahTerlambat,
                        'keterlambatan_menit'  => $totalTerlambat,
                        'keluar_awal_jumlah'   => $jumlahPulangCepat,
                        'keluar_awal_menit'    => $totalPulangCepat,
                        'realisasi_kehadiran'  => $hadir,
                        // Target kehadiran opsional, bisa dihitung: totalHari - (Minggu + Libur)
                    ]
                );
            }

            DB::commit();
            return back()->with('success', 'Rekap absensi bulanan berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function rekapitulasiFix1(Request $request)
{
    $request->validate([
        'bulan' => 'required',
        'tahun' => 'required',
    ]);

    $bulan = $request->bulan;
    $tahun = $request->tahun;

    $today = Carbon::today();

    // --- Tentukan akhir perhitungan hari ---
    if ($bulan == $today->month && $tahun == $today->year) {
        $totalHariBulanIni = $today->day; // bulan berjalan
    } else {
        $totalHariBulanIni = Carbon::create($tahun, $bulan)->daysInMonth;
    }

    // --- Ambil semua tanggal libur ---
    $listLibur = [];
    $events = Kalender::whereYear('waktu_mulai', $tahun)
                      ->whereMonth('waktu_mulai', $bulan)
                      ->get();

    foreach ($events as $event) {
        $end = $event->waktu_berakhir ?? $event->waktu_mulai;

        $period = CarbonPeriod::create($event->waktu_mulai, $end);

        foreach ($period as $date) {
            $listLibur[] = $date->format('Y-m-d');
        }
    }

    $karyawans = Karyawan::all();

    DB::beginTransaction();
    try {

        foreach ($karyawans as $karyawan) {

            // Ambil semua absen harian dalam 1 bulan
            $harian = AbsensiHarian::where('id_karyawan', $karyawan->id_karyawan)
                        ->whereYear('tanggal', $tahun)
                        ->whereMonth('tanggal', $bulan)
                        ->get()
                        ->keyBy(fn($item) => Carbon::parse($item->tanggal)->format('Y-m-d'));

            // Variabel hitungan
            $hadir = $sakit = $izin = $alpha = 0;
            $totalTerlambat = $jumlahTerlambat = 0;
            $totalPulangCepat = $jumlahPulangCepat = 0;

            // Loop semua hari
            for ($day = 1; $day <= $totalHariBulanIni; $day++) {

                $dateObj = Carbon::create($tahun, $bulan, $day);
                $dateStr = $dateObj->format('Y-m-d');

                $isSunday = $dateObj->isSunday();
                $isLiburKalender = in_array($dateStr, $listLibur);

                if (isset($harian[$dateStr])) {
                    $d = $harian[$dateStr];

                    if ($d->status_kehadiran == 'Hadir') $hadir++;
                    if ($d->status_kehadiran == 'Sakit') $sakit++;
                    if ($d->status_kehadiran == 'Izin')  $izin++;
                    if ($d->status_kehadiran == 'Tidak Hadir') $alpha++;

                    if ($d->terlambat_menit > 0) $jumlahTerlambat++;
                    $totalTerlambat += $d->terlambat_menit;

                    if ($d->pulang_cepat_menit > 0) $jumlahPulangCepat++;
                    $totalPulangCepat += $d->pulang_cepat_menit;

                } else {
                    // Tidak ada absen
                    if (!$isLiburKalender && !$isSunday) {
                        $alpha++; // alpha otomatis
                    }
                }
            }

            // Jangan simpan kalau benar-benar kosong (hindari bug 1)
            if ($hadir == 0 && $sakit == 0 && $izin == 0 && $alpha == 0) {
                continue;
            }

            Absensi::updateOrCreate(
                [
                    'id_karyawan' => $karyawan->id_karyawan,
                    'bulan'       => $bulan,
                    'tahun'       => $tahun,
                ],
                [
                    'nama'                 => $karyawan->nama,
                    'departemen'           => $karyawan->departemen,
                    'hadir'                => $hadir,
                    'sakit'                => $sakit,
                    'izin'                 => $izin,
                    'alpha'                => $alpha,
                    'keterlambatan_jumlah' => $jumlahTerlambat,
                    'keterlambatan_menit'  => $totalTerlambat,
                    'keluar_awal_jumlah'   => $jumlahPulangCepat,
                    'keluar_awal_menit'    => $totalPulangCepat,
                    'realisasi_kehadiran'  => $hadir,
                ]
            );
        }

        DB::commit();
        return back()->with('success', 'Rekap absensi bulanan berhasil dan alpha otomatis dihitung!');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}


    public function rekapitulasi1(Request $request)
    {
        $request->validate([
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        $bulan = $request->bulan;
        $tahun = $request->tahun;

        // 1. Ambil Data Libur dari Kalender di Bulan Tersebut
        // Kita simpan tanggal-tanggal libur ke dalam array agar pengecekan cepat
        $listLibur = [];
        $events = Kalender::whereYear('waktu_mulai', $tahun)
                          ->whereMonth('waktu_mulai', $bulan)
                          ->get();

        foreach ($events as $event) {
            // Jika waktu_berakhir null, anggap 1 hari saja
            $end = $event->waktu_berakhir ?? $event->waktu_mulai;
            // Buat range tanggal dari mulai sampai akhir
            $period = CarbonPeriod::create($event->waktu_mulai, $end);
            
            foreach ($period as $date) {
                // Simpan format Y-m-d ke array (Cth: 2025-12-13)
                $listLibur[] = $date->format('Y-m-d');
            }
        }

        // Hitung jumlah hari dalam bulan tersebut
        $totalHariBulanIni = Carbon::createFromDate($tahun, $bulan)->daysInMonth;

        $karyawans = Karyawan::all();

        DB::beginTransaction();
        try {
            foreach ($karyawans as $karyawan) {
                
                // Ambil data harian, tapi jadikan tanggal sebagai KEY agar mudah dicari
                // Format key: '2025-12-01', '2025-12-02', dst
                $harian = AbsensiHarian::where('id_karyawan', $karyawan->id_karyawan)
                            ->whereYear('tanggal', $tahun)
                            ->whereMonth('tanggal', $bulan)
                            ->get()
                            ->keyBy(function($item) {
                                return Carbon::parse($item->tanggal)->format('Y-m-d');
                            });

                // Inisialisasi Variabel Hitungan
                $totalTerlambatMenit = 0;
                $jumlahTerlambat     = 0;
                $totalPulangCepatMenit = 0;
                $jumlahPulangCepat   = 0;
                
                $hadirCount = 0;
                $sakitCount = 0;
                $izinCount  = 0;
                $alphaCount = 0;

                // 2. LOOPING SETIAP HARI (Tgl 1 s/d Akhir Bulan)
                for ($day = 1; $day <= $totalHariBulanIni; $day++) {
                    // Buat tanggal saat ini, misal: 2025-12-12
                    $currentDateObj = Carbon::createFromDate($tahun, $bulan, $day);
                    $currentDateStr = $currentDateObj->format('Y-m-d');

                    // Cek apakah tanggal ini hari Minggu? (Opsional: Jika minggu libur otomatis)
                    $isSunday = $currentDateObj->isSunday();

                    // CEK 1: Apakah ada data absen di tanggal ini?
                    if (isset($harian[$currentDateStr])) {
                        // --- ADA DATANYA ---
                        $dataHariIni = $harian[$currentDateStr];

                        // Hitung Status
                        if ($dataHariIni->status_kehadiran == 'Hadir') $hadirCount++;
                        if ($dataHariIni->status_kehadiran == 'Sakit') $sakitCount++;
                        if ($dataHariIni->status_kehadiran == 'Izin')  $izinCount++;
                        // Jika status manual 'Tidak Hadir' sudah diinput admin
                        if ($dataHariIni->status_kehadiran == 'Tidak Hadir') $alphaCount++; 

                        // Hitung Menit
                        $totalTerlambatMenit += $dataHariIni->terlambat_menit;
                        if ($dataHariIni->terlambat_menit > 0) $jumlahTerlambat++;

                        $totalPulangCepatMenit += $dataHariIni->pulang_cepat_menit;
                        if ($dataHariIni->pulang_cepat_menit > 0) $jumlahPulangCepat++;

                    } else {
                        // --- TIDAK ADA DATANYA (Potensi Alpha) ---
                        
                        // Cek apakah hari ini ada di List Libur Kalender?
                        $isLiburKalender = in_array($currentDateStr, $listLibur);

                        // LOGIKA PENENTUAN ALPHA:
                        // Jika TIDAK ada data absen DAN TIDAK libur kalender DAN BUKAN hari minggu
                        // (Hapus `&& !$isSunday` jika hari minggu tetap masuk kerja)
                        if (!$isLiburKalender && !$isSunday) {
                            $alphaCount++; // Hitung sebagai Alpha otomatis
                        }
                    }
                }

                // 3. Update atau Buat Data di Tabel Rekap (Absensi)
                Absensi::updateOrCreate(
                    [
                        'id_karyawan' => $karyawan->id_karyawan,
                        'bulan'       => $bulan,
                        'tahun'       => $tahun,
                    ],
                    [
                        'nama'                 => $karyawan->nama,
                        'departemen'           => $karyawan->departemen,
                        'keterlambatan_jumlah' => $jumlahTerlambat,
                        'keterlambatan_menit'  => $totalTerlambatMenit,
                        'keluar_awal_jumlah'   => $jumlahPulangCepat,
                        'keluar_awal_menit'    => $totalPulangCepatMenit,
                        'hadir'                => $hadirCount,
                        'sakit'                => $sakitCount,
                        'izin'                 => $izinCount,
                        'alpha'                => $alphaCount,
                        'realisasi_kehadiran'  => $hadirCount,
                    ]
                );
            }
            
            DB::commit();
            return redirect()->route('absensi.index', ['bulan' => $bulan, 'tahun' => $tahun])
                ->with('success', 'Rekapitulasi absensi harian berhasil ditarik! Alpha otomatis terhitung.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal rekap: ' . $e->getMessage());
        }
    }

    // --- LOGIKA UTAMA REKAPITULASI DARI HARIAN KE BULANAN ---
    public function rekapitulasi2(Request $request)
    {
        $request->validate([
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        $bulan = $request->bulan;
        $tahun = $request->tahun;

        // 1. Ambil semua karyawan
        $karyawans = Karyawan::all();

        DB::beginTransaction();
        try {
            foreach ($karyawans as $karyawan) {
                // 2. Ambil Data Harian Karyawan tersebut di Bulan & Tahun yg dipilih
                $harian = AbsensiHarian::where('id_karyawan', $karyawan->id_karyawan)
                            ->whereYear('tanggal', $tahun)
                            ->whereMonth('tanggal', $bulan)
                            ->get();

                // 3. Hitung Agregat
                $totalTerlambatMenit = $harian->sum('terlambat_menit');
                $jumlahTerlambat     = $harian->where('terlambat_menit', '>', 0)->count();
                
                $totalPulangCepatMenit = $harian->sum('pulang_cepat_menit');
                $jumlahPulangCepat     = $harian->where('pulang_cepat_menit', '>', 0)->count();

                // Hitung Status
                $hadirCount = $harian->where('status_kehadiran', 'Hadir')->count();
                $sakitCount = $harian->where('status_kehadiran', 'Sakit')->count();
                $izinCount  = $harian->where('status_kehadiran', 'Izin')->count();
                $alphaCount = $harian->where('status_kehadiran', 'Tidak Hadir')->count();

                // 4. Update atau Buat Data di Tabel Rekap (Absensi)
                Absensi::updateOrCreate(
                    [
                        'id_karyawan' => $karyawan->id_karyawan,
                        'bulan'       => $bulan,
                        'tahun'       => $tahun,
                    ],
                    [
                        'nama'                 => $karyawan->nama,
                        'departemen'           => $karyawan->departemen, // Asumsi ada kolom ini di Karyawan
                        'keterlambatan_jumlah' => $jumlahTerlambat,
                        'keterlambatan_menit'  => $totalTerlambatMenit,
                        'keluar_awal_jumlah'   => $jumlahPulangCepat,
                        'keluar_awal_menit'    => $totalPulangCepatMenit,
                        'hadir'                => $hadirCount,
                        'sakit'                => $sakitCount,
                        'izin'                 => $izinCount,
                        'alpha'                => $alphaCount,
                        'realisasi_kehadiran'  => $hadirCount, // Realisasi biasanya sama dengan hadir
                    ]
                );
            }
            
            DB::commit();
            return redirect()->route('absensi.index', ['bulan' => $bulan, 'tahun' => $tahun])
                ->with('success', 'Rekapitulasi absensi harian berhasil ditarik!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal rekap: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $absensi = Absensi::findOrFail($id);
        return view('absensi.edit', [
            'absensi' => $absensi,
            'bulan' => request('bulan'),
            'tahun' => request('tahun'),
        ]);
    }

    public function update(Request $request, $id)
    {
        // Update manual jika diperlukan (misal koreksi target)
        $absensi = Absensi::findOrFail($id);
        $absensi->update($request->all());

        return redirect()
            ->route('absensi.index', ['bulan' => $request->bulan, 'tahun' => $request->tahun])
            ->with('success', 'Data diperbarui.');
    }

    public function destroy($id)
    {
        $absensi = Absensi::findOrFail($id);
        $absensi->delete();
        return redirect()->back()->with('success', 'Data dihapus.');
    }
    
    // Import Form dll bisa dibiarkan jika masih mau support import Excel manual rekap
    public function importForm() { return view('absensi.import'); }
}