<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawans = Karyawan::all();
        return view('karyawans.index', compact('karyawans'));
    }

    public function dashboard()
    {
        $jumlahKaryawan = Karyawan::count();
        $totalGaji = Karyawan::sum('gaji_pokok');

        return view('dashboard', compact('jumlahKaryawan', 'totalGaji'));
    }

    public function create()
    {
        return view('karyawans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:255|unique:karyawans',
            'jabatan' => 'required|string|max:255',
            'gaji_pokok' => 'required|numeric',
        ]);

        Karyawan::create($validated);

        return redirect()->route('karyawans.index')->with('success', 'Karyawan berhasil ditambahkan');
    }

    public function edit(Karyawan $karyawan)
    {
        return view('karyawans.edit', compact('karyawan'));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:255|unique:karyawans,nip,' . $karyawan->id,
            'jabatan' => 'required|string|max:255',
            'gaji_pokok' => 'required|numeric',
        ]);

        $karyawan->update($validated);

        return redirect()->route('karyawans.index')->with('success', 'Karyawan berhasil diupdate');
    }

    public function destroy(Karyawan $karyawan)
    {
        $karyawan->delete();

        return redirect()->route('karyawans.index')->with('success', 'Karyawan berhasil dihapus');
    }
}
