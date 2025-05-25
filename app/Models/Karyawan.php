<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $fillable = ['nama', 'nip', 'jabatan', 'gaji_pokok'];
    public function absensis()
{
    return $this->hasMany(Absensi::class);
}

}
