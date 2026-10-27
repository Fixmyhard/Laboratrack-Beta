<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiHarian extends Model
{
    protected $table = 'absensi_harians';
    
    protected $fillable = [
        'id_karyawan',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'default_masuk',
        'default_pulang',
        'terlambat_menit',
        'pulang_cepat_menit',
        'status_kehadiran',
        'bulan',
        'tahun'
    ];

    // Relasi ke Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }

    // --- LOGIKA KONVERSI MENIT KE JAM (HELPER) ---
    private function formatJamMenit($totalMenit)
    {
        if (!$totalMenit || $totalMenit <= 0) {
            return '-'; 
        }

        $jam = floor($totalMenit / 60); 
        $menit = $totalMenit % 60;      

        $hasil = [];

        if ($jam > 0) {
            $hasil[] = "{$jam} jam";
        }

        if ($menit > 0) {
            $hasil[] = "{$menit} menit";
        }

        return implode(' ', $hasil);
    }

    // --- ACCESSOR ---
    // Panggil di blade dengan: $a->terlambat_teks
    public function getTerlambatTeksAttribute()
    {
        return $this->formatJamMenit($this->terlambat_menit);
    }

    // Panggil di blade dengan: $a->pulang_cepat_teks
    public function getPulangCepatTeksAttribute()
    {
        return $this->formatJamMenit($this->pulang_cepat_menit);
    }
}