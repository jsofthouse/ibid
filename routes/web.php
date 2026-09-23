<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FileStreamController;
use App\Http\Controllers\Admin\KaryaController;
use App\Http\Controllers\Admin\KaryaIbidController;
use App\Http\Controllers\Admin\KaryaStatusController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\LupaPasswordController;
use App\Http\Controllers\Admin\OrangController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\PengajuanController;
use App\Http\Controllers\Admin\PengajuanStatusController;
use App\Http\Controllers\Admin\ResetPasswordController;
use App\Http\Controllers\Publik\BukuController;
use App\Http\Controllers\Publik\CariController;
use App\Http\Controllers\Publik\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::get('cari', [CariController::class, 'index'])
    ->middleware('throttle:60,1')
    ->name('cari');

Route::get('buku/{ibid}', [BukuController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('buku.show');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store']);

        Route::get('lupa-password', [LupaPasswordController::class, 'create'])->name('lupa-password');
        Route::post('lupa-password', [LupaPasswordController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('lupa-password.kirim');

        Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('reset-password');
        Route::post('reset-password', [ResetPasswordController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('reset-password.simpan');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('password', [PasswordController::class, 'edit'])->name('password.edit');
        Route::put('password', [PasswordController::class, 'update'])->name('password.update');

        Route::get('log', [LogController::class, 'index'])->name('log');

        Route::resource('kategori', KategoriController::class)->except('show');
        Route::resource('orang', OrangController::class)->except('show');

        Route::resource('pengajuan', PengajuanController::class)->only(['index', 'show']);
        Route::post('pengajuan/{pengajuan}/status', [PengajuanStatusController::class, 'ubah'])
            ->name('pengajuan.status');

        Route::resource('karya', KaryaController::class);
        Route::post('karya/{karya}/status-produksi', [KaryaStatusController::class, 'ubahProduksi'])
            ->name('karya.status-produksi');
        Route::post('karya/{karya}/status-identitas', [KaryaStatusController::class, 'ubahIdentitas'])
            ->name('karya.status-identitas');
        Route::post('karya/{karya}/batalkan-penerbitan', [KaryaStatusController::class, 'batalkan'])
            ->name('karya.batalkan-penerbitan');

        Route::post('karya/{karya}/generate-ibid', [KaryaIbidController::class, 'generate'])
            ->name('karya.generate-ibid');
        Route::get('karya/{karya}/qr.svg', [KaryaIbidController::class, 'unduhSvg'])
            ->name('karya.qr-svg');
        Route::get('karya/{karya}/qr.png', [KaryaIbidController::class, 'unduhPng'])
            ->name('karya.qr-png');

        Route::get('berkas/{path}', [FileStreamController::class, 'show'])
            ->where('path', '.*')
            ->name('berkas');
    });
});
