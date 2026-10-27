<?php

namespace App\Imports;

use App\Models\AbsensiHarian;
use App\Models\Karyawan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets; 
use Illuminate\Support\Facades\Session;

class AbsensiHarianImport implements WithMultipleSheets 
{
    // Kita targetkan sheet bernama "Catatan"
    public function sheets(): array
    {
        return [
            'Catatan' => new class implements ToCollection {
                
                // Helper: Hitung selisih menit
                private function hitungMenit($jam1, $jam2) {
                    if (!$jam1 || !$jam2) return 0;
                    $t1 = strtotime($jam1);
                    $t2 = strtotime($jam2);
                    return ($t1 - $t2) / 60;
                }

                public function collection(Collection $rows)
                {
                    $insertedCount = 0;
                    $currentMonth = date('m'); // Default bulan ini
                    $currentYear = date('Y');  // Default tahun ini

                    // Loop semua baris di Excel
                    foreach ($rows as $index => $row) {
                        
                        // 1. CARI POSISI "Nama:" DI BARIS INI
                        $colNamaLabel = -1;
                        foreach ($row as $key => $cell) {
                            if (is_string($cell) && stripos($cell, 'Nama:') !== false) {
                                $colNamaLabel = $key;
                                break; 
                            }
                        }

                        // 2. JIKA KETEMU LABEL "NAMA:"
                        if ($colNamaLabel != -1) {
                            $namaKaryawan = null;
                            if (isset($row[$colNamaLabel + 2]) && trim($row[$colNamaLabel + 2]) != '') {
                                $namaKaryawan = trim($row[$colNamaLabel + 2]);
                            } elseif (isset($row[$colNamaLabel + 1]) && trim($row[$colNamaLabel + 1]) != '') {
                                $namaKaryawan = trim($row[$colNamaLabel + 1]);
                            }

                            if ($namaKaryawan) {
                                // Cari ID Karyawan di Database (Harus persis atau lowercase)
                                $karyawan = Karyawan::whereRaw('LOWER(nama) = ?', [strtolower($namaKaryawan)])->first();
                                
                                if ($karyawan) {
                                    // 3. AMBIL DATA JAM (DI BARIS BAWAHNYA / Index + 1)
                                    if (isset($rows[$index + 1])) {
                                        $timeRow = $rows[$index + 1];
                                        
                                        // Loop Tanggal 1 sampai 31 (Kolom A=Tgl 1, B=Tgl 2, dst)
                                        for ($day = 1; $day <= 31; $day++) {
                                            $colIndex = $day - 1; // Index array mulai dari 0
                                            
                                            if (isset($timeRow[$colIndex])) {
                                                // Bersihkan data
                                                $rawTime = trim(str_replace(['"', "\r", "\n"], ' ', $timeRow[$colIndex]));
                                                
                                                if (!empty($rawTime)) {
                                                    // Format jam di excel: "07:55 17:00"
                                                    $times = preg_split('/\s+/', $rawTime, -1, PREG_SPLIT_NO_EMPTY);
                                                    
                                                    $jamMasuk = isset($times[0]) ? $times[0] : null;
                                                    $jamPulang = (count($times) > 1) ? end($times) : null;

                                                    // Validasi minimal format jam
                                                    if ($jamMasuk && strlen($jamMasuk) >= 4) {
                                                        
                                                        $dateString = sprintf('%s-%s-%02d', $currentYear, $currentMonth, $day);
                                                        
                                                        // -----------------------------------------------------
                                                        // START PERBAIKAN LOGIKA TOLERANSI DISINI
                                                        // -----------------------------------------------------
                                                        $defaultMasuk = "07:30";
                                                        $defaultPulang = "16:00";
                                                        
                                                        $terlambat = 0;

                                                        if ($jamMasuk) {
                                                            // Tentukan batas toleransi (07:45)
                                                            $waktuMasuk = strtotime($jamMasuk);
                                                            $batasToleransi = strtotime("07:45:00");

                                                            // Jika Jam Masuk > 07:45
                                                            if ($waktuMasuk > $batasToleransi) {
                                                                // Hitung selisih dari 07:30
                                                                $diff = $this->hitungMenit($jamMasuk, $defaultMasuk);
                                                                if ($diff > 0) $terlambat = $diff;
                                                            } else {
                                                                // Jika 07:31 - 07:45, dianggap 0
                                                                $terlambat = 0;
                                                            }
                                                        }
                                                        // -----------------------------------------------------
                                                        // END PERBAIKAN
                                                        // -----------------------------------------------------

                                                        $pulangCepat = 0;
                                                        if ($jamPulang) {
                                                            $diff = $this->hitungMenit($defaultPulang, $jamPulang);
                                                            if ($diff > 0) $pulangCepat = $diff;
                                                        }

                                                        // Simpan ke Database
                                                        AbsensiHarian::updateOrCreate(
                                                            [
                                                                'id_karyawan' => $karyawan->id_karyawan,
                                                                'tanggal'     => $dateString,
                                                            ],
                                                            [
                                                                'jam_masuk'          => $jamMasuk,
                                                                'jam_pulang'         => $jamPulang,
                                                                'status_kehadiran'   => 'Hadir',
                                                                'default_masuk'      => $defaultMasuk,
                                                                'default_pulang'     => $defaultPulang,
                                                                'terlambat_menit'    => (int)$terlambat,
                                                                'pulang_cepat_menit' => (int)$pulangCepat
                                                            ]
                                                        );
                                                        $insertedCount++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    if ($insertedCount > 0) {
                        Session::flash('success', "Berhasil mengimport $insertedCount data harian!");
                    } else {
                        Session::flash('error', "Tidak ada data yang terbaca. Pastikan nama sheet adalah 'Catatan'.");
                    }
                }
            }
        ];
    }
}