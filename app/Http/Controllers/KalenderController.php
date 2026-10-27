<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kalender;
use App\Models\AbsensiHarian; // Import Absensi Harian
use App\Models\Karyawan;      // Import Karyawan
use App\Models\Absensi;       // Import Absensi Rekap
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class KalenderController extends Controller
{
    public function index()
    {
        return view('kalender.index');
    }

    public function getEvents()
    {
        $kegiatans = Kalender::all();
        
        $events = $kegiatans->map(function($k) {
            return [
                'id' => $k->id_kalender,
                'start' => $k->waktu_mulai,
                'end' => $k->waktu_berakhir,
                'backgroundColor' => $k->jenis_kegiatan == 'libur' ? '#ef4444' : '#3b82f6',
                'borderColor' => $k->jenis_kegiatan == 'libur' ? '#ef4444' : '#3b82f6',
                'extendedProps' => [
                    'jenis' => $k->jenis_kegiatan,
                    'catatan' => $k->catatan 
                ]
            ];
        });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $request->validate([
            'waktu_mulai' => 'required|date',
            'waktu_berakhir' => 'nullable|date',
            'jenis_kegiatan' => 'required',
            'catatan' => 'nullable|string' 
        ]);

        // --- VALIDASI MAX 3 EVENT PER HARI ---
        $jumlahEventHariIni = Kalender::whereDate('waktu_mulai', $request->waktu_mulai)->count();

        if ($jumlahEventHariIni >= 3) {
            return response()->json(['message' => 'Gagal! Maksimal 3 kegiatan per hari.'], 422);
        }

        $kalender = Kalender::create([
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_berakhir' => $request->waktu_berakhir,
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'catatan' => $request->catatan, 
        ]);

        // [BARU] SINKRONISASI OTOMATIS KE ABSENSI HARIAN
        // Jika yang ditambahkan adalah LIBUR, maka update absensi karyawan jadi Hadir
        if ($request->jenis_kegiatan == 'libur') {
            $this->sinkronisasiLiburKeAbsensi($kalender);
        }

        return response()->json(['message' => 'Event berhasil ditambahkan dan Absensi diperbarui']);
    }

    public function update(Request $request, $id)
    {
        $kegiatan = Kalender::findOrFail($id);
        
        $kegiatan->update($request->all());

        // [BARU] SINKRONISASI OTOMATIS KE ABSENSI HARIAN
        if ($kegiatan->jenis_kegiatan == 'libur') {
            $this->sinkronisasiLiburKeAbsensi($kegiatan);
        }

        return response()->json(['message' => 'Event updated dan Absensi diperbarui']);
    }

    public function destroy($id)
    {
        $kegiatan = Kalender::findOrFail($id);
        
        // Simpan data sebelum dihapus jika ingin revert (opsional, logic revert agak kompleks)
        // Untuk saat ini kita hapus saja eventnya.
        
        $kegiatan->delete();

        return response()->json(['message' => 'Event deleted']);
    }

    // ============================================================================
    // FUNGSI PRIVATE: UPDATE ABSENSI HARIAN SAAT LIBUR DITAMBAHKAN
    // ============================================================================
    private function sinkronisasiLiburKeAbsensi($kalender)
    {
        $start = Carbon::parse($kalender->waktu_mulai);
        $end = $kalender->waktu_berakhir ? Carbon::parse($kalender->waktu_berakhir) : $start->copy();
        
        $period = CarbonPeriod::create($start, $end);
        $karyawans = Karyawan::all();

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');

            // 1. SKIP HARI MINGGU
            if ($date->isSunday()) { continue; }

            foreach ($karyawans as $karyawan) {
                // Cari data absensi di tanggal libur ini
                $absen = AbsensiHarian::where('id_karyawan', $karyawan->id_karyawan)
                                      ->where('tanggal', $dateStr)
                                      ->first();

                // KONDISI A: Data Absensi Belum Ada -> Buat data Hadir (Default)
                if (!$absen) {
                    $this->createHadirLibur($karyawan->id_karyawan, $dateStr, $date);
                } 
                // KONDISI B: Data Ada tapi statusnya 'Tidak Hadir' (Alpha)
                // Timpa jadi Hadir karena ternyata hari ini Libur
                elseif ($absen->status_kehadiran == 'Tidak Hadir') {
                    $absen->update([
                        'status_kehadiran'   => 'Hadir',
                        'jam_masuk'          => '07:30:00',
                        'jam_pulang'         => '16:00:00',
                        'terlambat_menit'    => 0,
                        'pulang_cepat_menit' => 0,
                        'default_masuk'      => '07:30:00',
                        'default_pulang'     => '16:00:00',
                    ]);
                    
                    // Update Rekap Bulanan juga
                    $this->updateRekapKaryawan($karyawan->id_karyawan, $date->month, $date->year);
                }
                
                // KONDISI C: Jika statusnya sudah 'Hadir', 'Sakit', atau 'Izin', 
                // JANGAN DIUBAH. (Asumsi data real lebih prioritas).
            }
        }
    }

    private function createHadirLibur($idKaryawan, $dateStr, $dateObj)
    {
        AbsensiHarian::create([
            'id_karyawan'        => $idKaryawan,
            'tanggal'            => $dateStr,
            'status_kehadiran'   => 'Hadir', // Libur dianggap Hadir
            'jam_masuk'          => '07:30:00',
            'jam_pulang'         => '16:00:00',
            'terlambat_menit'    => 0,
            'pulang_cepat_menit' => 0,
            'default_masuk'      => '07:30:00',
            'default_pulang'     => '16:00:00',
            'bulan'              => $dateObj->month,
            'tahun'              => $dateObj->year,
        ]);

        // Update Rekap Bulanan
        $this->updateRekapKaryawan($idKaryawan, $dateObj->month, $dateObj->year);
    }

    // Copy dari AbsensiHarianController agar rekap juga terupdate realtime
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

        // Hitung target hari kerja unik
        $totalHariKerja = AbsensiHarian::whereYear('tanggal', $tahun)
                                ->whereMonth('tanggal', $bulan)
                                ->distinct('tanggal')
                                ->count('tanggal');

        Absensi::updateOrCreate(
            [
                'id_karyawan' => $id_karyawan,
                'bulan'       => $bulan,
                'tahun'       => $tahun,
            ],
            [
                'nama'                 => $karyawan->nama,
                'departemen'           => $karyawan->departemen,
                'dibutuhkan'           => $totalHariKerja,
                'target_kehadiran'     => $totalHariKerja,
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