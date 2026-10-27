<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    // Sesuaikan dengan migration Anda (id_absensi)
    protected $primaryKey = 'id_absensi';

    protected $fillable = [
        'id_karyawan',
        'no',
        'nama',
        'departemen',
        'dibutuhkan',
        'kenyataan',
        'keterlambatan_jumlah',
        'keterlambatan_menit',
        'keluar_awal_jumlah',
        'keluar_awal_menit',
        'target_kehadiran',
        'realisasi_kehadiran',
        'hadir', 
        'sakit', 
        'izin', 
        'alpha',
        'bulan',
        'tahun',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }

    // ----------------------------------------------------------------------
    // LOGIKA KONVERSI MENIT KE JAM
    // ----------------------------------------------------------------------
    
    /**
     * Fungsi Helper Private untuk mengubah angka menit jadi teks "X jam Y menit"
     */
    private function formatJamMenit($totalMenit)
    {
        if (!$totalMenit || $totalMenit <= 0) {
            return '-'; // Atau '0 menit' jika mau
        }

        $jam = floor($totalMenit / 60); // Ambil angka jam bulat (kebawah)
        $menit = $totalMenit % 60;      // Ambil sisa menit

        $hasil = [];

        if ($jam > 0) {
            $hasil[] = "{$jam} jam";
        }

        if ($menit > 0) {
            $hasil[] = "{$menit} menit";
        }

        return implode(' ', $hasil);
    }

    /**
     * Accessor untuk Keterlambatan
     * PERBAIKAN: Nama fungsi disesuaikan dengan panggilan di View ($a->terlambat_teks)
     * Format Laravel: get[NamaAtribute]Attribute
     */
    public function getTerlambatTeksAttribute()
    {
        return $this->formatJamMenit($this->keterlambatan_menit);
    }

    /**
     * Accessor untuk Keluar Awal
     * PERBAIKAN: Nama fungsi disesuaikan dengan panggilan di View ($a->pulang_cepat_teks)
     */
    public function getPulangCepatTeksAttribute()
    {
        return $this->formatJamMenit($this->keluar_awal_menit);
    }

    // private function formatJamMenit($totalMenit)
    // {
    //     if (!$totalMenit || $totalMenit <= 0) {
    //         return '-'; // Atau '0 menit' jika mau
    //     }

    //     $jam = floor($totalMenit / 60); // Ambil angka jam bulat (kebawah)
    //     $menit = $totalMenit % 60;      // Ambil sisa menit

    //     $hasil = [];

    //     if ($jam > 0) {
    //         $hasil[] = "{$jam} jam";
    //     }

    //     if ($menit > 0) {
    //         $hasil[] = "{$menit} menit";
    //     }

    //     return implode(' ', $hasil);
    // }

    /**
     * Accessor untuk Keterlambatan
     * Cara panggil di Blade: {{ $absensi->keterlambatan_teks }}
     */
    public function getKeterlambatanTeksAttribute()
    {
        return $this->formatJamMenit($this->keterlambatan_menit);
    }

    /**
     * Accessor untuk Keluar Awal
     * Cara panggil di Blade: {{ $absensi->keluar_awal_teks }}
     */
    public function getKeluarAwalTeksAttribute()
    {
        return $this->formatJamMenit($this->keluar_awal_menit);
    }
}