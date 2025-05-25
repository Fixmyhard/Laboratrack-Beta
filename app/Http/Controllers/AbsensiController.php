<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AbsensiImport;

class AbsensiController extends Controller
{
    public function importForm()
    {
        return view('absensi.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            Excel::import(new AbsensiImport, $request->file('file'));
        } catch (\Exception $e) {
            return redirect()->route('absensi.importForm')
                ->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }

        return redirect()->route('absensi.importForm')->with('success', 'Data absensi berhasil diimport!');
    }
}
