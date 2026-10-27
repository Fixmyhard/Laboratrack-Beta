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
        Schema::create('kalender', function (Blueprint $table) {
            $table->integer('id_kalender')->autoIncrement();    
            $table->date('waktu_mulai');    
            $table->date('waktu_berakhir')->nullable();
            $table->string('jenis_kegiatan')->default('libur');
            $table->string('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kalender');
    }
};
