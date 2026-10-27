<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Karyawan;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AbsensiImport;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        // $hari = $request->hari;
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $query = Absensi::query();

        // if( $hari) {
        //     $query->where('hari', $hari);
        // }

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
        $request->validate([
            'keterlambatan_jumlah' => 'nullable|integer|min:0',
            'keterlambatan_menit' => 'nullable|integer|min:0',
            'keluar_awal_jumlah' => 'nullable|integer|min:0',
            'keluar_awal_menit' => 'nullable|integer|min:0',
            'realisasi_kehadiran' => 'nullable|integer|min:0',
        ]);

        $absensi = Absensi::findOrFail($id);

        $absensi->update($request->only([
            'keterlambatan_jumlah',
            'keterlambatan_menit',
            'keluar_awal_jumlah',
            'keluar_awal_menit',
            'realisasi_kehadiran',
        ]));

        return redirect()
            ->route('absensi.index', [
                'bulan' => $request->bulan,
                'tahun' => $request->tahun
            ])
            ->with('success', 'Data absensi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $absensi = Absensi::findOrFail($id);
        $absensi->delete();

        return redirect()->route('absensi.index')->with('success', 'Data absensi berhasil dihapus.');
    }

    public function importForm()
    {
        return view('absensi.import');
    }

    public function import(Request $request)
    {
        // Validasi input
        $request->validate([
            'file'  => 'required|mimes:xlsx,xls,csv|max:5120',
            'bulan' => 'required|string',
            'tahun' => 'required|string',
        ]);

        // Ambil daftar nama karyawan dari DB, lowercase untuk validasi
        $daftarNamaKaryawan = Karyawan::pluck('nama')
            ->map(fn($n) => strtolower(trim($n)))
            ->toArray();

        // Ambil semua baris dari file Excel
        $sheets = Excel::toCollection(new AbsensiImport($request->bulan, $request->tahun), $request->file('file'));

        $namaBelumTerdaftar = [];

        foreach ($sheets as $sheet) {
            foreach ($sheet as $row) {
                $namaExcel = strtolower(trim($row['nama'] ?? ''));
                if ($namaExcel && !in_array($namaExcel, $daftarNamaKaryawan)) {
                    $namaBelumTerdaftar[] = $row['nama']; // simpan nama asli dari Excel
                }
            }
        }

        if (!empty($namaBelumTerdaftar)) {
            $namaBelumTerdaftar = array_unique($namaBelumTerdaftar);
            $namaList = implode(', ', $namaBelumTerdaftar);

            // Batalkan import, tampilkan pop-up error
            return redirect()->route('absensi.importForm')
                ->with('error', "Ada karyawan yang belum terdaftar di sistem: $namaList");
        }

        // Semua nama sudah terdaftar → import data
        DB::beginTransaction();
        try {
            Excel::import(new AbsensiImport($request->bulan, $request->tahun), $request->file('file'));
            DB::commit();
            return redirect()->route('absensi.index', [
                'bulan' => $request->bulan,
                'tahun' => $request->tahun
            ])->with('success', 'Data absensi berhasil diimport!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('absensi.importForm')
                ->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }

    public function importDarius(Request $request)
    {
        // 1. Validasi Input termasuk Bulan dan Tahun
        $request->validate([
            'file'  => 'required|mimes:xlsx,xls,csv|max:5120',
            'bulan' => 'required|string',
            'tahun' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            Excel::import(new AbsensiImport($request->bulan, $request->tahun), $request->file('file'));
            DB::commit();
            return redirect()->route('absensi.importForm')->with('success', 'Data absensi berhasil diimport!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();
            $errorMsg = 'Error Validasi Baris ' . $failures[0]->row() . ': ' . $failures[0]->errors()[0];
            return redirect()->route('absensi.importForm')->with('error', $errorMsg);
            
        } catch (\Exception $e) {
            DB::rollBack();
            // Cek error spesifik sheet
            if (str_contains($e->getMessage(), 'Sheet not found')) {
                return redirect()->route('absensi.importForm')
                    ->with('error', 'Gagal: Sheet bernama "Total" tidak ditemukan di file Excel.');
            }
            return redirect()->route('absensi.importForm')
                ->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }
}