<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AbsensiHarian;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AbsensiHarianImport;
use App\Models\Karyawan;
use App\Models\Kalender;
use App\Models\Absensi; 
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AbsensiHarianController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
$id_karyawan = $request->id_karyawan;

$karyawans_all = Karyawan::orderBy('nama', 'asc')->get();
        if (!$bulan || !$tahun) {
            return view('absensi_harian.index', [
                'data' => collect([]),
                'karyawans_all' => $karyawans_all,
                'bulan' => null,
                'tahun' => null

            ]);
        }

        // =========================================================================
        // BAGIAN 1: GENERATE DATA ABSENSI OTOMATIS
        // =========================================================================
        
        $today = Carbon::today();
        $startOfMonth = Carbon::createFromDate($tahun, $bulan, 1);
        
        if ($bulan == $today->month && $tahun == $today->year) {
            $endOfMonth = $today; 
        } else {
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
        }

        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);
        $karyawans = Karyawan::all();
        $liburDates = [];
        $events = Kalender::where('jenis_kegiatan', 'libur') 
            ->where(function($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('waktu_mulai', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                  ->orWhereBetween('waktu_berakhir', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                  ->orWhere(function($sub) use ($startOfMonth, $endOfMonth) {
                      $sub->where('waktu_mulai', '<', $startOfMonth->format('Y-m-d'))
                          ->where('waktu_berakhir', '>', $endOfMonth->format('Y-m-d'));
                  });
            })->get();

        foreach ($events as $event) {
            $start = Carbon::parse($event->waktu_mulai);
            $end = $event->waktu_berakhir ? Carbon::parse($event->waktu_berakhir) : $start->copy();
            $liburPeriod = CarbonPeriod::create($start, $end);
            foreach ($liburPeriod as $d) {
                $liburDates[] = $d->format('Y-m-d');
            }
        }

        // 2. LOOPING GENERATE
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            if ($date->isSunday()) { continue; }
            $isLibur = in_array($dateStr, $liburDates);

            foreach ($karyawans as $karyawan) {
                $exists = AbsensiHarian::where('id_karyawan', $karyawan->id_karyawan)
                                       ->where('tanggal', $dateStr)
                                       ->exists();

                if (!$exists) {
                    if ($isLibur) {
                        AbsensiHarian::create([
                            'id_karyawan'      => $karyawan->id_karyawan,
                            'tanggal'          => $dateStr,
                            'status_kehadiran' => 'Hadir',
                            'jam_masuk'        => '07:30:00',
                            'jam_pulang'       => '16:00:00',
                            'terlambat_menit'  => 0,
                            'pulang_cepat_menit'=> 0,
                            'default_masuk'    => '07:30:00',
                            'default_pulang'   => '16:00:00',
                            'bulan'            => $bulan,
                            'tahun'            => $tahun,
                        ]);
                    } else {
                        AbsensiHarian::create([
                            'id_karyawan'      => $karyawan->id_karyawan,
                            'tanggal'          => $dateStr,
                            'status_kehadiran' => 'Tidak Hadir',
                            'jam_masuk'        => null,
                            'jam_pulang'       => null,
                            'terlambat_menit'  => 0,
                            'pulang_cepat_menit'=> 0,
                            'default_masuk'    => '07:30:00',
                            'default_pulang'   => '16:00:00',
                            'bulan'            => $bulan,
                            'tahun'            => $tahun,
                        ]);
                    }
                }
            }
        }

        // --- BAGIAN 2: TAMPILKAN DATA (HANYA JIKA FILTER TERISI) ---
        // Karena di awal function sudah kita cek !$bulan || !$tahun return empty,
        // maka kode di bawah ini pasti aman dijalankan (filter pasti ada).
        
        $query = AbsensiHarian::with('karyawan')
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun);
$query->when($id_karyawan, function($q) use ($id_karyawan) {
        return $q->where('id_karyawan', $id_karyawan);
    });
        $data = $query->orderBy('tanggal', 'desc')
                      ->orderBy('id_karyawan', 'asc')
                      ->get();

        return view('absensi_harian.index', compact('data', 'bulan', 'tahun', 'karyawans_all', 'id_karyawan'));
    }
private function hitungKeterlambatan($jamMasukInput, $defaultMasukStr = '07:30:00')
{
    if (!$jamMasukInput) return 0;

    $jamMasuk = Carbon::parse($jamMasukInput);
    $batasMasuk = Carbon::parse($defaultMasukStr);

    $batasToleransi = $batasMasuk->copy()->addMinutes(15);


    if ($jamMasuk->gt($batasToleransi)) {
        return $batasMasuk->diffInMinutes($jamMasuk);
    }
    return 0;
}

// ---------------------------------------------------------
// UPDATE FUNCTION STORE
// ---------------------------------------------------------
public function store(Request $request)
{
    $request->validate([
        'id_karyawan'      => 'required|exists:karyawans,id_karyawan',
        'tanggal'          => 'required|date',
        'status_kehadiran' => 'required|in:Hadir,Sakit,Izin,Tidak Hadir',
    ]);

    $defaultMasukStr  = '07:30:00';
    $defaultPulangStr = '16:00:00';
    
    $terlambatMenit = 0;
    $pulangCepatMenit = 0;

    if ($request->status_kehadiran == 'Hadir' && $request->jam_masuk) {
        $terlambatMenit = $this->hitungKeterlambatan($request->jam_masuk, $defaultMasukStr);
        if ($request->jam_pulang) {
            $jamPulang   = Carbon::parse($request->jam_pulang);
            $batasPulang = Carbon::parse($defaultPulangStr);
            if ($jamPulang->lt($batasPulang)) {
                $pulangCepatMenit = $jamPulang->diffInMinutes($batasPulang);
            }
        }
    }

    $dateObj = Carbon::parse($request->tanggal);

    AbsensiHarian::create([
        'id_karyawan'        => $request->id_karyawan,
        'tanggal'            => $request->tanggal,
        'status_kehadiran'   => $request->status_kehadiran,
        'jam_masuk'          => $request->jam_masuk,
        'jam_pulang'         => $request->jam_pulang,
        'default_masuk'      => $defaultMasukStr,
        'default_pulang'     => $defaultPulangStr,
        'terlambat_menit'    => $terlambatMenit, // Hasil perhitungan baru...
        'pulang_cepat_menit' => $pulangCepatMenit,
        'bulan'              => $dateObj->month,
        'tahun'              => $dateObj->year,
    ]);

    $this->updateRekapKaryawan($request->id_karyawan, $dateObj->month, $dateObj->year);

    return redirect()->route('absensi_harian.index')
        ->with('success', 'Data berhasil ditambahkan dengan toleransi keterlambatan 15 menit.');
}

// ---------------------------------------------------------
// UPDATE FUNCTION UPDATE
// ---------------------------------------------------------
public function update(Request $request, $id)
{
    $request->validate([
        'tanggal'          => 'required|date',
        'status_kehadiran' => 'required|in:Hadir,Sakit,Izin,Tidak Hadir',
    ]);

    $absensi = AbsensiHarian::findOrFail($id);

    $defaultMasukStr  = $absensi->default_masuk ?? '07:30:00';
    $defaultPulangStr = $absensi->default_pulang ?? '16:00:00';

    $terlambatMenit = 0;
    $pulangCepatMenit = 0;

    if ($request->status_kehadiran == 'Hadir' && $request->jam_masuk) {
        $terlambatMenit = $this->hitungKeterlambatan($request->jam_masuk, $defaultMasukStr);

        if ($request->jam_pulang) {
            $jamPulang   = Carbon::parse($request->jam_pulang);
            $batasPulang = Carbon::parse($defaultPulangStr);
            if ($jamPulang->lt($batasPulang)) {
                $pulangCepatMenit = $jamPulang->diffInMinutes($batasPulang);
            }
        }
    }

    $absensi->update([
        'tanggal'            => $request->tanggal,
        'jam_masuk'          => $request->jam_masuk,
        'jam_pulang'         => $request->jam_pulang,
        'terlambat_menit'    => $terlambatMenit,
        'pulang_cepat_menit' => $pulangCepatMenit,
        'status_kehadiran'   => $request->status_kehadiran,
    ]);

    $dateObj = Carbon::parse($request->tanggal);
    $this->updateRekapKaryawan($absensi->id_karyawan, $dateObj->month, $dateObj->year);

    return redirect()->route('absensi_harian.index')->with('success', 'Data diperbarui.');
}

    public function index1(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        // =========================================================================
        // BAGIAN 1: GENERATE DATA ABSENSI OTOMATIS (SINKRONISASI)
        // =========================================================================
        if ($bulan && $tahun) {
            
            $today = Carbon::today();
            $startOfMonth = Carbon::createFromDate($tahun, $bulan, 1);
            
            // Tentukan batas akhir (Hari ini jika bulan berjalan, atau akhir bulan)
            if ($bulan == $today->month && $tahun == $today->year) {
                $endOfMonth = $today; 
            } else {
                $endOfMonth = $startOfMonth->copy()->endOfMonth();
            }

            $period = CarbonPeriod::create($startOfMonth, $endOfMonth);
            $karyawans = Karyawan::all();

            // 1. AMBIL DATA TANGGAL LIBUR DARI KALENDER
            // Kita ambil event yang jenisnya 'libur' yang bersinggungan dengan bulan ini
            $liburDates = [];
            $events = Kalender::where('jenis_kegiatan', 'libur') // Filter khusus Libur
                ->where(function($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('waktu_mulai', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                      ->orWhereBetween('waktu_berakhir', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                      ->orWhere(function($sub) use ($startOfMonth, $endOfMonth) {
                          $sub->where('waktu_mulai', '<', $startOfMonth->format('Y-m-d'))
                              ->where('waktu_berakhir', '>', $endOfMonth->format('Y-m-d'));
                      });
                })->get();

            // Masukkan semua tanggal merah ke array $liburDates
            foreach ($events as $event) {
                $start = Carbon::parse($event->waktu_mulai);
                $end = $event->waktu_berakhir ? Carbon::parse($event->waktu_berakhir) : $start->copy();
                
                $liburPeriod = CarbonPeriod::create($start, $end);
                foreach ($liburPeriod as $d) {
                    $liburDates[] = $d->format('Y-m-d');
                }
            }

            // 2. LOOPING SETIAP HARI (LOGIKA UTAMA)
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                
                // ATURAN 3: JIKA HARI MINGGU, SKIP
                if ($date->isSunday()) { 
                    continue; 
                }

                // Cek apakah tanggal ini ada di daftar libur?
                $isLibur = in_array($dateStr, $liburDates);

                foreach ($karyawans as $karyawan) {
                    // Cek apakah data sudah ada (Hadir/Sakit/Izin/Alpha manual)?
                    $exists = AbsensiHarian::where('id_karyawan', $karyawan->id_karyawan)
                                           ->where('tanggal', $dateStr)
                                           ->exists();

                    // JIKA BELUM ADA DATA SAMA SEKALI
                    if (!$exists) {
                        
                        if ($isLibur) {
                            // -----------------------------------------------------------
                            // ATURAN 2: JIKA TANGGAL MERAH / LIBUR
                            // -----------------------------------------------------------
                            // Buat status "Hadir" dengan jam default.
                            // Supaya gaji TIDAK DIPOTONG.
                            AbsensiHarian::create([
                                'id_karyawan'      => $karyawan->id_karyawan,
                                'tanggal'          => $dateStr,
                                'status_kehadiran' => 'Hadir', // Dianggap Hadir
                                'jam_masuk'        => '07:30:00',
                                'jam_pulang'       => '16:00:00',
                                'terlambat_menit'  => 0,
                                'pulang_cepat_menit'=> 0,
                                'default_masuk'    => '07:30:00',
                                'default_pulang'   => '16:00:00',
                                'bulan'            => $bulan,
                                'tahun'            => $tahun,
                            ]);
                        } else {
                            // -----------------------------------------------------------
                            // ATURAN 1: JIKA HARI KERJA BIASA (TIDAK LIBUR)
                            // -----------------------------------------------------------
                            // Buat status "Tidak Hadir" (Alpha).
                            // Ini akan memotong gaji.
                            AbsensiHarian::create([
                                'id_karyawan'      => $karyawan->id_karyawan,
                                'tanggal'          => $dateStr,
                                'status_kehadiran' => 'Tidak Hadir', // Alpha
                                'jam_masuk'        => null,
                                'jam_pulang'       => null,
                                'terlambat_menit'  => 0,
                                'pulang_cepat_menit'=> 0,
                                'default_masuk'    => '07:30:00',
                                'default_pulang'   => '16:00:00',
                                'bulan'            => $bulan,
                                'tahun'            => $tahun,
                            ]);
                        }
                    }
                }
            }
        }

        // --- BAGIAN 2: TAMPILKAN DATA ---
        $query = AbsensiHarian::with('karyawan');

        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }
        if ($tahun) {
            $query->whereYear('tanggal', $tahun);
        }

        $data = $query->orderBy('tanggal', 'desc')
                      ->orderBy('id_karyawan', 'asc')
                      ->get();

        return view('absensi_harian.index', compact('data', 'bulan', 'tahun'));
    }

    // =========================================================================
    // FUNCTION LAIN (Import, Create, Edit, Update, Destroy) - TIDAK BERUBAH
    // =========================================================================

    public function importForm()
    {
        return view('absensi_harian.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new AbsensiHarianImport, $request->file('file'));
            return redirect()->route('absensi_harian.index')
                ->with('success', 'Data berhasil diimport. Silakan refresh halaman untuk melihat generate otomatis.'); 
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $absensi = AbsensiHarian::findOrFail($id);
        
        $idKaryawan = $absensi->id_karyawan;
        $bulan = $absensi->bulan ?? Carbon::parse($absensi->tanggal)->month;
        $tahun = $absensi->tahun ?? Carbon::parse($absensi->tanggal)->year;

        $absensi->delete();

        // Update Rekap
        $this->updateRekapKaryawan($idKaryawan, $bulan, $tahun);

        return redirect()->route('absensi_harian.index')->with('success', 'Data berhasil dihapus.');
    }

    public function create()
    {
        $karyawan = Karyawan::orderBy('nama', 'asc')->get();
        return view('absensi_harian.create', compact('karyawan'));
    }

    public function store2(Request $request)
    {
        $request->validate([
            'id_karyawan'      => 'required|exists:karyawans,id_karyawan',
            'tanggal'          => 'required|date',
            'status_kehadiran' => 'required|in:Hadir,Sakit,Izin,Tidak Hadir',
            'jam_masuk'        => 'nullable',
            'jam_pulang'       => 'nullable',
        ]);

        $defaultMasukStr  = '07:30:00';
        $defaultPulangStr = '16:00:00';
        $terlambatMenit = 0;
        $pulangCepatMenit = 0;

        $dateObj = Carbon::parse($request->tanggal);

        if ($request->status_kehadiran == 'Hadir' && $request->jam_masuk) {
            $jamMasuk   = Carbon::parse($request->jam_masuk);
            $batasMasuk = Carbon::parse($defaultMasukStr);
            
            $batasToleransi = $batasMasuk->copy()->addMinutes(15); // 07:45:00

            
            if ($jamMasuk->gt($batasToleransi)) {
                $terlambatMenit = $batasMasuk->diffInMinutes($jamMasuk);
            }

            if ($request->jam_pulang) {
                $jamPulang   = Carbon::parse($request->jam_pulang);
                $batasPulang = Carbon::parse($defaultPulangStr);
                if ($jamPulang->lt($batasPulang)) {
                    $pulangCepatMenit = $jamPulang->diffInMinutes($batasPulang);
                }
            }
        }

        AbsensiHarian::create([
            'id_karyawan'        => $request->id_karyawan,
            'tanggal'            => $request->tanggal,
            'status_kehadiran'   => $request->status_kehadiran,
            'jam_masuk'          => $request->jam_masuk,
            'jam_pulang'         => $request->jam_pulang,
            'default_masuk'      => $defaultMasukStr,
            'default_pulang'     => $defaultPulangStr,
            'terlambat_menit'    => $terlambatMenit,
            'pulang_cepat_menit' => $pulangCepatMenit,
            'bulan'              => $dateObj->month,
            'tahun'              => $dateObj->year,
        ]);

        $this->updateRekapKaryawan($request->id_karyawan, $dateObj->month, $dateObj->year);

        return redirect()->route('absensi_harian.index')
            ->with('success', 'Data absensi berhasil ditambahkan.');
    }

    public function update2(Request $request, $id)
    {
        $request->validate([
            'tanggal'          => 'required|date',
            'jam_masuk'        => 'nullable', 
            'jam_pulang'       => 'nullable', 
            'status_kehadiran' => 'required|in:Hadir,Sakit,Izin,Tidak Hadir',
        ]);

        $absensi = AbsensiHarian::findOrFail($id);

        $defaultMasukStr  = $absensi->default_masuk ?? '07:30:00';
        $defaultPulangStr = $absensi->default_pulang ?? '16:00:00';

        $terlambatMenit = 0;
        $pulangCepatMenit = 0;

        if ($request->status_kehadiran == 'Hadir' && $request->jam_masuk) {
            $jamMasuk   = Carbon::parse($request->jam_masuk);
            $batasMasuk = Carbon::parse($defaultMasukStr); 
            $batasToleransi = $batasMasuk->copy()->addMinutes(15); 

            if ($jamMasuk->gt($batasToleransi)) {
                $terlambatMenit = $batasMasuk->diffInMinutes($jamMasuk);
            }

            if ($request->jam_pulang) {
                $jamPulang   = Carbon::parse($request->jam_pulang);
                $batasPulang = Carbon::parse($defaultPulangStr); 
                if ($jamPulang->lt($batasPulang)) {
                    $pulangCepatMenit = $jamPulang->diffInMinutes($batasPulang);
                }
            }
        }

        $absensi->update([
            'tanggal'            => $request->tanggal,
            'jam_masuk'          => $request->jam_masuk,
            'jam_pulang'         => $request->jam_pulang,
            'terlambat_menit'    => $terlambatMenit,
            'pulang_cepat_menit' => $pulangCepatMenit,
            'status_kehadiran'   => $request->status_kehadiran,
        ]);

        $dateObj = Carbon::parse($request->tanggal);
        $this->updateRekapKaryawan($absensi->id_karyawan, $dateObj->month, $dateObj->year);

        return redirect()->route('absensi_harian.index')
            ->with('success', 'Data diperbarui.');
    }

    public function store1(Request $request)
    {
        $request->validate([
            'id_karyawan'      => 'required|exists:karyawans,id_karyawan',
            'tanggal'          => 'required|date',
            'status_kehadiran' => 'required|in:Hadir,Sakit,Izin,Tidak Hadir',
            'jam_masuk'        => 'nullable',
            'jam_pulang'       => 'nullable',
        ]);

        $defaultMasukStr  = '07:30:00';
        $defaultPulangStr = '16:00:00';
        $terlambatMenit = 0;
        $pulangCepatMenit = 0;

        // Hitung manual untuk store
        if ($request->status_kehadiran == 'Hadir' && $request->jam_masuk) {
            $jamMasuk   = Carbon::parse($request->jam_masuk);
            $batasMasuk = Carbon::parse($defaultMasukStr);
            $batasToleransi = $batasMasuk->copy()->addMinutes(15);

            if ($jamMasuk->gt($batasToleransi)) {
                $terlambatMenit = $batasToleransi->diffInMinutes($jamMasuk);
            }

            if ($request->jam_pulang) {
                $jamPulang   = Carbon::parse($request->jam_pulang);
                $batasPulang = Carbon::parse($defaultPulangStr);
                if ($jamPulang->lt($batasPulang)) {
                    $pulangCepatMenit = $jamPulang->diffInMinutes($batasPulang);
                }
            }
        }

        $dateObj = Carbon::parse($request->tanggal);

        AbsensiHarian::create([
            'id_karyawan'        => $request->id_karyawan,
            'tanggal'            => $request->tanggal,
            'status_kehadiran'   => $request->status_kehadiran,
            'jam_masuk'          => $request->jam_masuk,
            'jam_pulang'         => $request->jam_pulang,
            'default_masuk'      => $defaultMasukStr,
            'default_pulang'     => $defaultPulangStr,
            'terlambat_menit'    => $terlambatMenit,
            'pulang_cepat_menit' => $pulangCepatMenit,
            'bulan'              => $dateObj->month,
            'tahun'              => $dateObj->year,
        ]);

        $this->updateRekapKaryawan($request->id_karyawan, $dateObj->month, $dateObj->year);

        return redirect()->route('absensi_harian.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $absensi = AbsensiHarian::with('karyawan')->findOrFail($id);
        return view('absensi_harian.edit', compact('absensi'));
    }

    public function update1(Request $request, $id)
    {
        $request->validate([
            'tanggal'          => 'required|date',
            'jam_masuk'        => 'nullable', 
            'jam_pulang'       => 'nullable', 
            'status_kehadiran' => 'required|in:Hadir,Sakit,Izin,Tidak Hadir',
        ]);

        $absensi = AbsensiHarian::findOrFail($id);

        $defaultMasukStr  = $absensi->default_masuk ?? '07:30:00';
        $defaultPulangStr = $absensi->default_pulang ?? '16:00:00';

        $terlambatMenit = 0;
        $pulangCepatMenit = 0;

        if ($request->status_kehadiran == 'Hadir' && $request->jam_masuk) {
            $jamMasuk   = Carbon::parse($request->jam_masuk);
            $batasMasuk = Carbon::parse($defaultMasukStr); 
            $batasToleransi = $batasMasuk->copy()->addMinutes(15); 

            if ($jamMasuk->gt($batasToleransi)) {
                $terlambatMenit = $batasToleransi->diffInMinutes($jamMasuk);
            }

            if ($request->jam_pulang) {
                $jamPulang   = Carbon::parse($request->jam_pulang);
                $batasPulang = Carbon::parse($defaultPulangStr); 
                if ($jamPulang->lt($batasPulang)) {
                    $pulangCepatMenit = $jamPulang->diffInMinutes($batasPulang);
                }
            }
        }

        $absensi->update([
            'tanggal'            => $request->tanggal,
            'jam_masuk'          => $request->jam_masuk,
            'jam_pulang'         => $request->jam_pulang,
            'terlambat_menit'    => $terlambatMenit,
            'pulang_cepat_menit' => $pulangCepatMenit,
            'status_kehadiran'   => $request->status_kehadiran,
        ]);

        $dateObj = Carbon::parse($request->tanggal);
        $this->updateRekapKaryawan($absensi->id_karyawan, $dateObj->month, $dateObj->year);

        return redirect()->route('absensi_harian.index')
            ->with('success', 'Data diperbarui.');
    }

    private function updateRekapKaryawan($id_karyawan, $bulan, $tahun)
    {
        $karyawan = Karyawan::find($id_karyawan);
        if (!$karyawan) return;

        $harian = AbsensiHarian::where('id_karyawan', $id_karyawan)
                               ->whereMonth('tanggal', $bulan)
                               ->whereYear('tanggal', $tahun)
                               ->get();

        $hadir = $harian->where('status_kehadiran', 'Hadir')->count();
        $sakit = $harian->where('status_kehadiran', 'Sakit')->count();
        $izin  = $harian->where('status_kehadiran', 'Izin')->count();
        $alpha = $harian->where('status_kehadiran', 'Tidak Hadir')->count();

        $totalTerlambatMenit = $harian->sum('terlambat_menit');
        $jumlahTerlambat     = $harian->where('terlambat_menit', '>', 0)->count();
        
        $totalPulangCepatMenit = $harian->sum('pulang_cepat_menit');
        $jumlahPulangCepat     = $harian->where('pulang_cepat_menit', '>', 0)->count();

        Absensi::updateOrCreate(
            [
                'id_karyawan' => $id_karyawan,
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
                'keterlambatan_menit'  => $totalTerlambatMenit,
                'keluar_awal_jumlah'   => $jumlahPulangCepat,
                'keluar_awal_menit'    => $totalPulangCepatMenit,
                'realisasi_kehadiran'  => $hadir,
            ]
        );
    }
}