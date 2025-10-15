<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dinkes\DashboardController as DinkesDashboardController;
use App\Http\Controllers\Puskesmas\DashboardController as PuskesmasDashboardController;
use App\Http\Controllers\Bidan\DashboardController as BidanDashboardController;
use App\Http\Controllers\Rs\DashboardController as RsDashboardController;
use App\Http\Controllers\Pasien\DashboardController as PasienDashboardController;
use App\Http\Controllers\Dinkes\ProfileController;



Route::get('/', fn() => view('auth.login'));

Route::middleware(['auth'])->group(function () {

    Route::middleware('role:dinkes')
        ->prefix('dinkes')->as('dinkes.')
        ->group(function () {
            // Dashboard
            Route::get('/dashboard', [DinkesDashboardController::class, 'index'])->name('dashboard');

            // Data Master
            Route::get('/data-master', [\App\Http\Controllers\Dinkes\DataMasterController::class, 'index'])
                ->name('data-master');

            // Form create (berdasarkan tab)
            Route::get('/data-master/create', [\App\Http\Controllers\Dinkes\DataMasterController::class, 'create'])
                ->name('data-master.create');

            // Store per tab
            Route::post('/data-master/store-bidan', [\App\Http\Controllers\Dinkes\DataMasterController::class, 'storeBidan'])
                ->name('data-master.store-bidan');

            Route::post('/data-master/store-rs', [\App\Http\Controllers\Dinkes\DataMasterController::class, 'storeRs'])
                ->name('data-master.store-rs');

            Route::post('/data-master/store-puskesmas', [\App\Http\Controllers\Dinkes\DataMasterController::class, 'storePuskesmas'])
                ->name('data-master.store-puskesmas');

            // Aksi baris
            Route::post('/data-master/{user}/reset-password', [\App\Http\Controllers\Dinkes\DataMasterController::class, 'resetPassword'])
                ->name('data-master.reset');
            Route::get('/data-master/{user}', [\App\Http\Controllers\Dinkes\DataMasterController::class, 'show'])
                ->name('data-master.show');
            Route::get('/data-master/{user}/edit', [\App\Http\Controllers\Dinkes\DataMasterController::class, 'edit'])
                ->name('data-master.edit');
            Route::put('/data-master/{user}', [\App\Http\Controllers\Dinkes\DataMasterController::class, 'update'])
                ->name('data-master.update');
            Route::delete('/data-master/{user}', [\App\Http\Controllers\Dinkes\DataMasterController::class, 'destroy'])
                ->name('data-master.destroy');

            // List Pengajuan Akun (Akun Baru)
            Route::get('/akun-baru', [\App\Http\Controllers\Dinkes\AkunBaruController::class, 'index'])
                ->name('akun-baru');

            Route::post('/akun-baru', [\App\Http\Controllers\Dinkes\AkunBaruController::class, 'store'])
                ->name('akun-baru.store');

            Route::post('/akun-baru/{id}/approve', [\App\Http\Controllers\Dinkes\AkunBaruController::class, 'approve'])
                ->name('akun-baru.approve');

            Route::delete('/akun-baru/{id}', [\App\Http\Controllers\Dinkes\AkunBaruController::class, 'reject'])
                ->name('akun-baru.reject');

            // Pasien Nifas
            route::get('/pasien-nifas', [\App\Http\Controllers\Dinkes\PasienNifasController::class, 'index'])
                ->name('pasien-nifas');

            // Profile
            Route::get('/profile', [\App\Http\Controllers\Dinkes\ProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [\App\Http\Controllers\Dinkes\ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile/photo', [\App\Http\Controllers\Dinkes\ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');

        });

    Route::middleware('role:puskesmas')
        ->prefix('puskesmas')->as('puskesmas.')
        ->group(function () {
            Route::get('/dashboard', [PuskesmasDashboardController::class, 'index'])->name('dashboard');
        });

    Route::middleware('role:bidan')
        ->prefix('bidan')->as('bidan.')
        ->group(function () {
            Route::get('/dashboard', [BidanDashboardController::class, 'index'])->name('dashboard');
        });

    Route::middleware('role:rumah_sakit')
        ->prefix('rs')->as('rs.')
        ->group(function () {
            Route::get('/dashboard', [RsDashboardController::class, 'index'])->name('dashboard');
        });

    Route::middleware('role:pasien')
        ->prefix('pasien')->as('pasien.')
        ->group(function () {
            Route::get('/dashboard', [PasienDashboardController::class, 'index'])->name('dashboard');
        });
});


require __DIR__ . '/auth.php';
