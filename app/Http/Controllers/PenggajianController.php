<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penggajian;
use App\Models\Karyawan;
use App\Models\AbsensiHarian;
use App\Models\Kalender;
use App\Models\Absensi;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PenggajianController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $dataGaji = Penggajian::with('karyawan')
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->get();

        return view('penggajian.index', compact('dataGaji', 'bulan', 'tahun'));
    }
    public function cetak(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        // Ambil data sama persis seperti index
        $dataGaji = Penggajian::with('karyawan')
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->get();

        // Nama bulan untuk judul laporan (contoh: "Januari")
        $namaBulan = Carbon::createFromDate(null, $bulan, 1)->translatedFormat('F');

        return view('penggajian.cetak', compact('dataGaji', 'bulan', 'tahun', 'namaBulan'));
    }
    public function hitungGaji(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $dendaPerJam = 12000; // Nominal denda

        // 2. Ambil Data Karyawan yang memiliki Gaji Pokok
        $karyawans = Karyawan::where('gaji_pokok', '>', 0)->get();

        // 3. Ambil Data Rekap Absensi (Source of Truth)
        $dataRekap = Absensi::where('bulan', $bulan)
                            ->where('tahun', $tahun)
                            ->get()
                            ->keyBy('id_karyawan');

        // Cek: Jika data rekap kosong, minta user rekap dulu
        if ($dataRekap->isEmpty()) {
            return redirect()->back()->with('error', 'Data Rekap Absensi belum ditemukan. Silakan masuk menu Absensi Bulanan -> klik tombol "Rekapitulasi/Sinkronisasi" terlebih dahulu.');
        }

        DB::    beginTransaction();
        try {
            // 4. Bersihkan data gaji lama di bulan & tahun yang sama (Reset)
            Penggajian::where('bulan', $bulan)->where('tahun', $tahun)->delete();

            foreach ($karyawans as $karyawan) {
                
                // --- [GET] AMBIL GAJI POKOK DARI MASTER KARYAWAN ---
                // Kita simpan ke variabel agar nilainya terkunci untuk proses ini
                $gajiPokokSaatIni = $karyawan->gaji_pokok;

                $jamTerlambat = 0;
                $jamPulangCepat = 0;
                $jamAlpa = 0;
                $jumlahAlpa = 0;

                // Cek apakah karyawan ini ada di data rekap?
                if (isset($dataRekap[$karyawan->id_karyawan])) {
                    $rekap = $dataRekap[$karyawan->id_karyawan];

                    // --- A. Hitung Jam Terlambat ---
                    if ($rekap->keterlambatan_menit > 0) {
                        $jamTerlambat = ceil($rekap->keterlambatan_menit / 60);
                    }

                    // --- B. Hitung Jam Pulang Cepat ---
                    if ($rekap->keluar_awal_menit > 0) {
                        $jamPulangCepat = ceil($rekap->keluar_awal_menit / 60);
                    }

                    // --- C. Hitung Jam Alpa ---
                    $jumlahAlpa = $rekap->alpha; 
                    if ($jumlahAlpa > 0) {
                        $jamAlpa = $jumlahAlpa * 9;
                    }
                } 
                // Jika tidak ada di rekap, variabel jam tetap 0 (sesuai kode lama Anda)

                // --- TOTAL POTONGAN ---
                $totalJamPotongan = $jamTerlambat + $jamPulangCepat + $jamAlpa;
                
                // Konversi ke Rupiah
                $totalUangPotongan = $totalJamPotongan * $dendaPerJam;

                // --- GAJI BERSIH ---
                // Menggunakan variabel $gajiPokokSaatIni yang sudah kita GET di atas
                $gajiBersih = $gajiPokokSaatIni - $totalUangPotongan;

                // Safety: Pastikan gaji tidak minus
                if ($gajiBersih < 0) $gajiBersih = 0;

                // --- [SET] SIMPAN KE TABEL PENGGAJIAN ---
                Penggajian::create([
                    'id_karyawan'         => $karyawan->id_karyawan,
                    'bulan'               => $bulan,
                    'tahun'               => $tahun,
                    
                    // Ini bagian penting: Menyimpan Gaji Pokok sebagai History (Snapshot)
                    'gaji_pokok'          => $gajiPokokSaatIni, 
                    
                    'total_potongan'      => $totalUangPotongan,
                    'total_gaji'          => $gajiBersih,
                    'jumlah_alpa'         => $jumlahAlpa,
                    'jumlah_terlambat'    => $jamTerlambat,      
                    'jumlah_pulang_cepat' => $jamPulangCepat, 
                ]);
            }

            DB::commit();
            return redirect()->route('penggajian.index', ['bulan' => $bulan, 'tahun' => $tahun])
                ->with('success', 'Gaji berhasil dihitung!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Hitung jam kerja full (pembulatan ke atas)
     */
    private function hitungJamFullDay($masuk, $pulang)
    {
        try {
            // Normalisasi: ganti titik jadi titik dua (07.30 -> 07:30)
            $masukStr = str_replace('.', ':', $masuk);
            $pulangStr = str_replace('.', ':', $pulang);
            
            $start = Carbon::parse($masukStr);
            $end = Carbon::parse($pulangStr);
            
            $diffMinutes = $end->diffInMinutes($start);
            
            // Aturan: Denda dibulatkan ke atas per jam
            // Contoh: 8.5 jam kerja -> Denda 9 jam
            return ceil($diffMinutes / 60);
        } catch (\Exception $e) {
            return 9; // Fallback jika format jam salah (9 jam)
        }
    }
    public function dashboardGaji()
    {
        // 1. Tentukan Bulan & Tahun saat ini
        $bulan = date('m');
        $tahun = date('Y');

        // 2. Hitung Jumlah Karyawan
        $jumlahKaryawan = Karyawan::count();

        // 3. HITUNG TOTAL GAJI BERSIH (Sesuai Request)
        // Mengambil sum('total_gaji') dari tabel penggajian
        // Filter berdasarkan bulan & tahun agar yang muncul hanya tagihan bulan ini
        $totalGaji = Penggajian::where('bulan', $bulan)
                                ->where('tahun', $tahun)
                                ->sum('total_gaji');

        // Jika ingin total semua gaji sepanjang masa (tanpa filter bulan), gunakan:
        // $totalGaji = Penggajian::sum('total_gaji');

        return view('dashboard', compact('jumlahKaryawan', 'totalGaji'));
    }
    public function slipGaji($id)
{
    // Mengambil data gaji berdasarkan ID beserta relasi karyawannya
    $gaji = Penggajian::with('karyawan')->findOrFail($id);
    
    // Nama bulan untuk tampilan
    $namaBulan = Carbon::createFromDate(null, $gaji->bulan, 1)->translatedFormat('F');

    return view('penggajian.slip', compact('gaji', 'namaBulan'));
}
}