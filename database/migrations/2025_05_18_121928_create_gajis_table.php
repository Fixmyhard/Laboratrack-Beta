<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gajis', function (Blueprint $table) {
            //id_gaji sebagai primary key
            // id_karyawan sebagai foreign key
            $table->date('periode_awal');
            $table->date('periode_akhir');
            $table->decimal('gaji_dibayar', 12, 2);
            $table->integer('jumlah_hadir');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gajis');
    }
};
