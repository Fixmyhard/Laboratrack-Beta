<?php

namespace App\Imports;

use App\Models\Absensi;
use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;

class AbsensiImport implements WithMultipleSheets
{
    protected $bulan;
    protected $tahun;

    public function __construct($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function sheets(): array
    {
        return [
            'Total' => new class($this->bulan, $this->tahun) implements ToModel, WithStartRow {

                private $bulan, $tahun;

                public function __construct($bulan, $tahun)
                {
                    $this->bulan = $bulan;
                    $this->tahun = $tahun;
                }

                public function startRow(): int
                {
                    return 5;
                }

                public function model(array $row)
                {
                    if (!isset($row[1]) || trim($row[1]) === '') return null;

                    $nama = trim($row[1]);

                    $karyawan = Karyawan::firstOrCreate(
                        ['nama' => $nama],
                        ['jabatan' => $row[2] ?? '-', 'gaji_pokok' => 0]
                    );

                    $dibutuhkan = floatval(str_replace(',', '.', $row[3] ?? 0));
                    $kenyataan  = floatval(str_replace(',', '.', $row[4] ?? 0));

                    $rawKehadiran = $row[9] ?? '0/0';
                    $parts = explode('/', $rawKehadiran);
                    $target = intval($parts[0] ?? 0);
                    $realisasi = intval($parts[1] ?? 0);

                    $data = [
                        'no' => $row[0] ?? null,
                        'nama' => $nama,
                        'departemen' => $row[2] ?? '-',
                        'dibutuhkan' => $dibutuhkan,
                        'kenyataan' => $kenyataan,
                        'keterlambatan_jumlah' => intval($row[5] ?? 0),
                        'keterlambatan_menit' => intval($row[6] ?? 0),
                        'keluar_awal_jumlah' => intval($row[7] ?? 0),
                        'keluar_awal_menit' => intval($row[8] ?? 0),
                        'target_kehadiran' => $target,
                        'realisasi_kehadiran' => $realisasi,
                        'tidak_hadir' => intval($row[10] ?? 0),
                        'bulan' => $this->bulan,
                        'tahun' => $this->tahun,
                        'id_karyawan' => $karyawan->id_karyawan
                    ];

                    Absensi::updateOrCreate(
                        ['id_karyawan' => $karyawan->id_karyawan, 'bulan' => $this->bulan, 'tahun' => $this->tahun],
                        $data
                    );

                    return null;
                }
            }
        ];
    }
}
