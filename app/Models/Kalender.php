<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kalender extends Model
{
    use HasFactory;

    protected $table = 'kalender';

    protected $primaryKey = 'id_kalender';

    protected $fillable = [
        'waktu_mulai',     
        'waktu_berakhir',  
        'jenis_kegiatan',
        'catatan'
    ];
}
