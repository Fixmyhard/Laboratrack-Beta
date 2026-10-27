<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index()
    {
        // Menggunakan 'with' agar query ke database lebih hemat (Eager Loading)
        // $karyawans = Karyawan::with('absensi')->get();
        $karyawans = Karyawan::with('absensi')
            ->orderBy('jabatan', 'asc')
            ->orderBy('nama', 'asc')
            ->get();
        return view('karyawans.index', compact('karyawans'));
    }

    public function dashboard()
    {
        // 1. Hitung total jumlah karyawan
        $jumlahKaryawan = Karyawan::count();
        
        // 2. Ambil data karyawan + absensinya
        $karyawans = Karyawan::with('absensi')->get();
        
        // 3. Hitung Total Gaji yang harus dibayar bulan ini
        // Fungsi sum() di sini pintar, dia akan memanggil aksesors 'total_gaji' 
        // di model Karyawan (yang rumusnya: realisasi_kehadiran * 96000)
        $totalGaji = $karyawans->sum('total_gaji');

        return view('dashboard', compact('jumlahKaryawan', 'totalGaji'));
    }

    public function create()
    {
        // return view('karyawans.create');
        $daftarJabatan = Karyawan::daftarJabatan();
        return view('karyawans.create', compact('daftarJabatan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255,unique:karyawans,nama',
            'jabatan' => 'required|string|max:255',
            'gaji_pokok' => 'required|numeric',
        ]);

        Karyawan::create($validated);
        return redirect()->route('karyawans.index')->with('success', 'Karyawan berhasil ditambahkan');
    }

    // public function edit(Karyawan $karyawan)
    // {
    //     return view('karyawans.edit', compact('karyawan'));
    // }

    public function edit($id_karyawan)
    {
        $karyawan = Karyawan::findOrFail($id_karyawan);
        $daftarJabatan = Karyawan::daftarJabatan();
        return view('karyawans.edit', compact('karyawan', 'daftarJabatan'));
    }

    // public function update(Request $request, Karyawan $karyawan)
    // {
    //     $validated = $request->validate([
    //         'nama' => 'required|string|max:255,unique:karyawans,nama'. $karyawan->id_karyawan . ',id_karyawan',
    //         'jabatan' => 'required|string|max:255',
    //         'gaji_pokok' => 'required|numeric',
    //     ]);

    //     $karyawan->update($validated);
    //     return redirect()->route('karyawans.index')->with('success', 'Karyawan berhasil diupdate');
    // }

    public function update(Request $request, $id_karyawan)
    {
        $karyawan = Karyawan::findOrFail($id_karyawan);

        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:karyawans,nama,' . $karyawan->id_karyawan . ',id_karyawan',
            'jabatan' => 'required|string|max:255',
            'gaji_pokok' => 'required|numeric',
        ]);

        $karyawan->update($validated);

        return redirect()->route('karyawans.index')->with('success', 'Karyawan berhasil diupdate');
    }

    // public function destroy(Karyawan $karyawan)
    // {
    //     $karyawan->delete();
    //     return redirect()->route('karyawans.index')->with('success', 'Karyawan berhasil dihapus');
    // }

    public function destroy($id_karyawan)
    {
        $karyawan = Karyawan::findOrFail($id_karyawan);
        $karyawan->delete();

        return redirect()->route('karyawans.index')->with('success', 'Karyawan berhasil dihapus');
    }
}