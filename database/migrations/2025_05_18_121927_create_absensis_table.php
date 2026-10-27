<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->integer('id_absensi')->autoIncrement();
            $table->integer('id_karyawan')->nullable();
            $table->foreign('id_karyawan')->references('id_karyawan')->on('karyawans')->onDelete('cascade'); 

            $table->integer('no')->nullable();
            $table->string('nama');
            $table->string('departemen')->nullable();

            $table->decimal('dibutuhkan', 8, 2)->default(0);
            $table->decimal('kenyataan', 8, 2)->default(0); 
            $table->integer('keterlambatan_jumlah')->nullable();
            $table->integer('keterlambatan_menit')->nullable();
            $table->integer('keluar_awal_jumlah')->nullable();
            $table->integer('keluar_awal_menit')->nullable();
            $table->integer('target_kehadiran')->default(0);
            $table->integer('realisasi_kehadiran')->default(0);
            $table->integer('tidak_hadir')->nullable();
            $table->string('bulan')->nullable(); 
            $table->string('tahun')->nullable(); 

            $table->integer('hadir')->default(0);
            $table->integer('sakit')->default(0);
            $table->integer('izin')->default(0);
            $table->integer('alpha')->default(0); 

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};