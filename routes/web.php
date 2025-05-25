<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\AbsensiController;

Route::get('/absensi/import', [AbsensiController::class, 'importForm'])->name('absensi.importForm');
Route::post('/absensi/import', [AbsensiController::class, 'import'])->name('absensi.import');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [KaryawanController::class, 'dashboard'])->name('dashboard');

    Route::resource('karyawans', KaryawanController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/', function () {
    return view('welcome');
});

require __DIR__.'/auth.php';
