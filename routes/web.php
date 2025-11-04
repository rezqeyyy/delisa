<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Dinkes\DashboardController as DinkesDashboardController;
use App\Http\Controllers\Puskesmas\DashboardController as PuskesmasDashboardController;
use App\Http\Controllers\Bidan\DashboardController as BidanDashboardController;
use App\Http\Controllers\Rs\DashboardController as RsDashboardController;
use App\Http\Controllers\Pasien\DashboardController as PasienDashboardController;
use App\Http\Controllers\Pasien\SkriningController as PasienSkriningController;
use App\Http\Controllers\Dinkes\DataMasterController;
use App\Http\Controllers\Dinkes\AkunBaruController;
use App\Http\Controllers\Dinkes\PasienNifasController;
use App\Http\Controllers\Dinkes\ProfileController;
use App\Http\Controllers\Dinkes\PasienController;
use App\Http\Controllers\WilayahController;

/*
|--------------------------------------------------------------------------
| Home / Landing
|--------------------------------------------------------------------------
| Satu saja. Jika sudah login, arahkan ke dashboard sesuai role.
| Jika belum, tampilkan halaman login pasien (UI publik).
*/

Route::get('/', function () {
    if (Auth::check()) {
        $role = optional(Auth::user()->role)->nama_role;

        return match ($role) {
            'dinkes'      => redirect()->route('dinkes.dashboard'),
            'puskesmas'   => redirect()->route('puskesmas.dashboard'),
            'bidan'       => redirect()->route('bidan.dashboard'),
            'rumah_sakit' => redirect()->route('rs.dashboard'),
            'pasien'      => redirect()->route('pasien.dashboard'),
            default       => redirect()->route('login'), // dari auth.php (Breeze)
        };
    }

    return view('auth.login-pasien');
});

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // ================== DINKES ==================
    Route::middleware('role:dinkes')
        ->prefix('dinkes')->as('dinkes.')
        ->group(function () {
            // Dashboard
            Route::get('/dashboard', [DinkesDashboardController::class, 'index'])->name('dashboard');
            Route::get('/pasien/{pasien}', [PasienController::class, 'show'])
                ->name('pasien.show');
            // Data Master
            Route::get('/data-master', [DataMasterController::class, 'index'])->name('data-master');
            Route::get('/data-master/create', [DataMasterController::class, 'create'])->name('data-master.create');

            // Store per tab
            Route::post('/data-master/store-bidan', [DataMasterController::class, 'storeBidan'])->name('data-master.store-bidan');
            Route::post('/data-master/store-rs', [DataMasterController::class, 'storeRs'])->name('data-master.store-rs');
            Route::post('/data-master/store-puskesmas', [DataMasterController::class, 'storePuskesmas'])->name('data-master.store-puskesmas');

            // Aksi baris
            Route::post('/data-master/{user}/reset-password', [DataMasterController::class, 'resetPassword'])->name('data-master.reset');
            Route::get('/data-master/{user}', [DataMasterController::class, 'show'])->name('data-master.show');
            Route::get('/data-master/{user}/edit', [DataMasterController::class, 'edit'])->name('data-master.edit');
            Route::put('/data-master/{user}', [DataMasterController::class, 'update'])->name('data-master.update');
            Route::delete('/data-master/{user}', [DataMasterController::class, 'destroy'])->name('data-master.destroy');

            // Akun Baru
            Route::get('/akun-baru', [AkunBaruController::class, 'index'])->name('akun-baru');
            Route::post('/akun-baru', [AkunBaruController::class, 'store'])->name('akun-baru.store');
            Route::post('/akun-baru/{id}/approve', [AkunBaruController::class, 'approve'])->name('akun-baru.approve');
            Route::delete('/akun-baru/{id}', [AkunBaruController::class, 'reject'])->name('akun-baru.reject');

            // Pasien Nifas
            Route::get('/pasien-nifas', [PasienNifasController::class, 'index'])->name('pasien-nifas');
            Route::delete('/pasien-nifas/{pasien}', [PasienNifasController::class, 'destroy'])->name('pasien-nifas.destroy');


            // Profile
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');

            
        });


    // ================== PUSKESMAS ==================
    Route::middleware('role:puskesmas')
        ->prefix('puskesmas')->as('puskesmas.')
        ->group(function () {
        Route::get('/dashboard', [PuskesmasDashboardController::class, 'index'])->name('dashboard');
        Route::get('/skrining', [\App\Http\Controllers\Puskesmas\SkriningController::class, 'index'])->name('skrining');
        Route::get('/laporan', [\App\Http\Controllers\Puskesmas\LaporanController::class, 'index'])->name('laporan');
        Route::get('/pasien-nifas', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'index'])->name('pasien-nifas');
        });

    // ================== BIDAN ==================
    Route::middleware('role:bidan')
        ->prefix('bidan')->as('bidan.')
        ->group(function () {
            Route::get('/dashboard', [BidanDashboardController::class, 'index'])->name('dashboard');
            Route::get('/skrining', [\App\Http\Controllers\Bidan\SkriningController::class, 'index'])->name('skrining');

            // 1. Route untuk halaman detail (tujuan setelah klik "Ya")
            Route::get('/skrining/{skrining}', [\App\Http\Controllers\Bidan\SkriningController::class, 'show'])
                ->name('skrining.show');

            // 2. Route untuk update status "checked" (saat "Ya" diklik)
            Route::post('/skrining/{skrining}/mark-as-viewed', [\App\Http\Controllers\Bidan\SkriningController::class, 'markAsViewed'])
                ->name('skrining.markAsViewed');

            // 3. Route untuk tombol "Sudah Diperiksa"
            Route::post('/skrining/{skrining}/follow-up', [\App\Http\Controllers\Bidan\SkriningController::class, 'followUp'])
                ->name('skrining.followUp');
        });

    // ================== RUMAH SAKIT ==================
    Route::middleware('role:rumah_sakit')
        ->prefix('rs')->as('rs.')
        ->group(function () {
            Route::get('/dashboard', [RsDashboardController::class, 'index'])->name('dashboard');
        });

    // ================== PASIEN ==================
    Route::middleware('role:pasien')
        ->prefix('pasien')->as('pasien.')
        ->group(function () {
            Route::get('/dashboard', [PasienDashboardController::class, 'index'])->name('dashboard');
            Route::get('/puskesmas/search', [PasienSkriningController::class, 'puskesmasSearch'])->name('puskesmas.search');

            // Pemilihan puskesmas via modal
            Route::post('/skrining/ajukan/puskesmas', [PasienSkriningController::class, 'storePengajuan'])
                ->name('skrining.pengajuan.store');

            // Data Diri
            Route::get('/skrining/ajukan', [PasienSkriningController::class, 'create'])->name('data-diri');
            Route::post('/skrining/ajukan', [PasienSkriningController::class, 'storeDataDiri'])->name('data-diri.store');

            // Riwayat Kehamilan & Persalinan (GPA)
            Route::get('/skrining/riwayat-kehamilan-gpa', [PasienSkriningController::class, 'riwayatKehamilanGpa'])
                ->name('riwayat-kehamilan-gpa');
            Route::post('/skrining/riwayat-kehamilan-gpa', [PasienSkriningController::class, 'storeRiwayatKehamilanGpa'])
                ->name('riwayat-kehamilan-gpa.store');

            // Kondisi Kesehatan Pasien
            Route::get('/skrining/kondisi-kesehatan-pasien', [PasienSkriningController::class, 'kondisiKesehatanPasien'])
                ->name('kondisi-kesehatan-pasien');
            Route::post('/skrining/kondisi-kesehatan-pasien', [PasienSkriningController::class, 'storeKondisiKesehatanPasien'])
                ->name('kondisi-kesehatan-pasien.store');

            // Riwayat Penyakit Pasien
            Route::get('/skrining/riwayat-penyakit-pasien', [PasienSkriningController::class, 'riwayatPenyakitPasien'])
                ->name('riwayat-penyakit-pasien');
            Route::post('/skrining/riwayat-penyakit-pasien', [PasienSkriningController::class, 'storeRiwayatPenyakitPasien'])
                ->name('riwayat-penyakit-pasien.store');

            // Riwayat Penyakit Keluarga
            Route::get('/skrining/riwayat-penyakit-keluarga', [PasienSkriningController::class, 'riwayatPenyakitKeluarga'])
                ->name('riwayat-penyakit-keluarga');
            Route::post('/skrining/riwayat-penyakit-keluarga', [PasienSkriningController::class, 'storeRiwayatPenyakitKeluarga'])
                ->name('riwayat-penyakit-keluarga.store');

            // Preeklampsia
            Route::get('/skrining/preeklampsia', [PasienSkriningController::class, 'preEklampsia'])
                ->name('preeklampsia');
            Route::post('/skrining/preeklampsia', [PasienSkriningController::class, 'storePreEklampsia'])
                ->name('preeklampsia.store');


            Route::get('/skrining/{skrining}', [PasienSkriningController::class, 'show'])->name('skrining.show');
            Route::get('/skrining/{skrining}/edit', [PasienSkriningController::class, 'edit'])->name('skrining.edit');
            Route::delete('/skrining/{skrining}', [PasienSkriningController::class, 'destroy'])->name('skrining.destroy');
        });

    Route::prefix('wilayah')->group(function () {
        Route::get('provinces', [WilayahController::class, 'provinces'])->name('wilayah.provinces');
        Route::get('regencies/{provId}', [WilayahController::class, 'regencies'])->name('wilayah.regencies');
        Route::get('districts/{kabId}', [WilayahController::class, 'districts'])->name('wilayah.districts');
        Route::get('villages/{kecId}', [WilayahController::class, 'villages'])->name('wilayah.villages');
    });
});

require __DIR__ . '/auth.php';
