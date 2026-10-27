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
        Schema::create('penggajian', function (Blueprint $table) {
            $table->integer('id_gaji')->autoIncrement();
            $table->integer('id_karyawan');
            $table->foreign('id_karyawan')->references('id_karyawan')->on('karyawans')->onDelete('cascade'); 
            $table->string('bulan'); // 01, 02, ..., 12
            $table->string('tahun'); // 2024, 2025, ...
            $table->decimal('gaji_pokok', 15, 2)->default(0);     // Gaji dasar
            $table->decimal('total_potongan', 15, 2)->default(0); // Total denda (terlambat + alpa)
            $table->decimal('total_gaji', 15, 2)->default(0);     // Gaji bersih (pokok - potongan)
            $table->integer('jumlah_alpa')->default(0);         // Jumlah hari alpa
            $table->integer('jumlah_terlambat')->default(0);    // Total jam terlambat (hasil pembulatan)
            $table->integer('jumlah_pulang_cepat')->default(0); // Total jam pulang cepat (hasil pembulatan)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggajian');
    }
};
