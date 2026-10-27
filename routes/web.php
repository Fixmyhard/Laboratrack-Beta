<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AbsensiHarianController; // Pastikan Controller ini di-import
use App\Http\Controllers\KalenderController;
use App\Http\Controllers\PenggajianController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/bantuan', function () {
    return view('bantuan');
})->name('bantuan');

// ==========================================
// RUTE DASHBOARD & AUTH
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [KaryawanController::class, 'dashboard'])->name('dashboard');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Slip Gaji
    Route::get('/penggajian/slip/{id}', [PenggajianController::class, 'slipGaji'])->name('penggajian.slip');
    // ==========================================
    // 1. RUTE ABSENSI HARIAN (Sesuai Folder absensi_harian)
    // ==========================================
    // Index
    Route::get('/absensi-harian', [AbsensiHarianController::class, 'index'])
        ->name('absensi_harian.index');

    // Import
    Route::get('/absensi-harian/import', [AbsensiHarianController::class, 'importForm'])
        ->name('absensi_harian.importForm');
    Route::post('/absensi-harian/import', [AbsensiHarianController::class, 'import'])
        ->name('absensi_harian.import');

    Route::get('/absensi-harian/create', [AbsensiHarianController::class, 'create'])
        ->name('absensi_harian.create');
    Route::post('/absensi-harian', [AbsensiHarianController::class, 'store'])
        ->name('absensi_harian.store');

    // Edit 
    Route::get('/absensi-harian/{id}/edit', [AbsensiHarianController::class, 'edit'])
        ->name('absensi_harian.edit');
    
    // Update
    Route::put('/absensi-harian/{id}', [AbsensiHarianController::class, 'update'])
        ->name('absensi_harian.update');
    
    // Delete
    Route::delete('/absensi-harian/{id}', [AbsensiHarianController::class, 'destroy'])
        ->name('absensi_harian.destroy');

    Route::get('/dashboard', [PenggajianController::class, 'dashboardGaji'])
       ->middleware(['auth', 'verified'])
        ->name('dashboard');

    // ==========================================
    // 2. RUTE ABSENSI BULANAN (Yg lama)
    // ==========================================
    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::get('/absensi/import', [AbsensiController::class, 'importForm'])->name('absensi.importForm');
    Route::post('/absensi/import', [AbsensiController::class, 'import'])->name('absensi.import');
    Route::get('/absensi/{id}/edit', [AbsensiController::class, 'edit'])->name('absensi.edit');
    Route::put('/absensi/{id}', [AbsensiController::class, 'update'])->name('absensi.update');
    Route::delete('/absensi/{id}', [AbsensiController::class, 'destroy'])->name('absensi.destroy');
    Route::post('/absensi/rekapitulasi', [AbsensiController::class, 'rekapitulasiFix'])->name('absensi.rekapitulasi');
    Route::get('/absensi/cetak', [AbsensiController::class, 'cetak'])->name('absensi.cetak');

    // ==========================================
    // 3. RUTE KARYAWAN
    // ==========================================
    Route::get('/karyawans', [KaryawanController::class, 'index'])->name('karyawans.index');
    Route::get('/karyawans/create', [KaryawanController::class, 'create'])->name('karyawans.create');
    Route::post('/karyawans', [KaryawanController::class, 'store'])->name('karyawans.store');
    Route::get('/karyawans/{id_karyawan}/edit', [KaryawanController::class, 'edit'])->name('karyawans.edit');
    Route::put('/karyawans/{id_karyawan}', [KaryawanController::class, 'update'])->name('karyawans.update');
    Route::delete('/karyawans/{id_karyawan}', [KaryawanController::class, 'destroy'])->name('karyawans.destroy');

    // ==========================================
    // 4. RUTE KALENDER
    // ==========================================
    Route::get('/kalender', [KalenderController::class, 'index'])->name('kalender.index');
    Route::get('/kalender/events', [KalenderController::class, 'getEvents'])->name('kalender.events');
    Route::post('/kalender', [KalenderController::class, 'store'])->name('kalender.store');
    Route::put('/kalender/{id}', [KalenderController::class, 'update'])->name('kalender.update');
    Route::delete('/kalender/{id}', [KalenderController::class, 'destroy'])->name('kalender.destroy');

    Route::get('/penggajian', [PenggajianController::class, 'index'])->name('penggajian.index');
    Route::post('/penggajian/hitung', [PenggajianController::class, 'hitungGaji'])->name('penggajian.hitung');
    Route::get('/penggajian/cetak', [PenggajianController::class, 'cetak'])->name('penggajian.cetak');
});

require __DIR__.'/auth.php';