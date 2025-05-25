<?php

namespace App\Imports;

use App\Models\Absensi;
use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class AbsensiImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $karyawan = Karyawan::where('nama', $row['nama_karyawan'])->first();

        if (!$karyawan) {
            return null;
        }

        return new Absensi([
            'karyawan_id' => $karyawan->id,
            'tanggal'     => Carbon::parse($row['tanggal'])->format('Y-m-d'),
            'jam_masuk'   => $row['jam_masuk'] ?? null,
            'jam_keluar'  => $row['jam_keluar'] ?? null,
        ]);
    }
}
