<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawans';
    protected $primaryKey = 'id_karyawan';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['nama', 'jabatan', 'gaji_pokok'];
    // public function absensi()
    // {
    //     return $this->hasOne(Absensi::class, 'nama', 'nama');
    // }

    public function absensi()
    {
        return $this->hasOne(Absensi::class, 'id_karyawan', 'id_karyawan');
    }

    /**
     * ATRIBUT: Hitung Total Gaji Otomatis
     * Logika: Jumlah Kehadiran Realisasi (dari tabel absensi) x Rp 96.000
     */
    public function getTotalGajiAttribute()
    {
        $absensi = $this->absensi;

        if (!$absensi) {
            return 0;
        }

        $ratePerKehadiran = 96000;
        $jumlahHadir = $absensi->realisasi_kehadiran;

        return $jumlahHadir * $ratePerKehadiran;
    }

    public function getTotalGajiRupiahAttribute()
    {
        return 'Rp ' . number_format($this->total_gaji, 0, ',', '.');
    }

    public static function daftarJabatan()
    {
        return [
            'Tukang',
            'Kenek',
            'Semi Tukang',
        ];
    }
}