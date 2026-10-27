<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_harians', function (Blueprint $table) {
            $table->id();
            $table->integer('id_karyawan');
            $table->foreign('id_karyawan')->references('id_karyawan')->on('karyawans')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('default_masuk')->default('07.30');
            $table->string('default_pulang')->default('16:00');
            $table->integer('terlambat_menit')->default(0);
            $table->integer('pulang_cepat_menit')->default(0);
            $table->string('jam_masuk')->nullable();
            $table->string('jam_pulang')->nullable();
             $table->string('bulan')->nullable(); 
            $table->string('tahun')->nullable();
            
            $table->string('status_kehadiran')->default('Hadir'); 

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_harians');
    }
};