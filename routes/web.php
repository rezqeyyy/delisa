<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Dinkes\DashboardController as DinkesDashboardController;
use App\Http\Controllers\Puskesmas\DashboardController as PuskesmasDashboardController;
use App\Http\Controllers\Bidan\DashboardController as BidanDashboardController;
use App\Http\Controllers\Rs\DashboardController as RsDashboardController;
use App\Http\Controllers\Rs\SkriningController as RsSkriningController;
use App\Http\Controllers\Rs\PasienNifasController as RsPasienNifasController;
use App\Http\Controllers\Pasien\DashboardController as PasienDashboardController;
use App\Http\Controllers\Pasien\SkriningController as PasienSkriningController;

use App\Http\Controllers\Dinkes\AkunBaruController;
use App\Http\Controllers\Dinkes\DataMasterController;
use App\Http\Controllers\Dinkes\PasienNifasController;
use App\Http\Controllers\Dinkes\ProfileController as DinkesProfileController;
use App\Http\Controllers\Dinkes\PasienController;

use App\Http\Controllers\Bidan\ProfileController as BidanProfileController; // <-- TAMBAHAN

use App\Http\Controllers\WilayahController;
use App\Http\Controllers\Pasien\Skrining\DataDiriController;
use App\Http\Controllers\Pasien\Skrining\RiwayatKehamilanGPAController;
use App\Http\Controllers\Pasien\Skrining\KondisiKesehatanPasienController;
use App\Http\Controllers\Pasien\Skrining\RiwayatPenyakitPasienController;
use App\Http\Controllers\Pasien\Skrining\RiwayatPenyakitKeluargaController;
use App\Http\Controllers\Pasien\Skrining\PreeklampsiaController;
use App\Http\Controllers\Dinkes\AnalyticsController;

/*
|--------------------------------------------------------------------------
| Home / Landing
|--------------------------------------------------------------------------
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
            default       => redirect()->route('login'),
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
            Route::get('/pasien/{pasien}', [PasienController::class, 'show'])->name('pasien.show');

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

            // Profile Dinkes
            Route::get('/profile', [DinkesProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [DinkesProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile/photo', [DinkesProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');

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
            // Dashboard
            Route::get('/dashboard', [BidanDashboardController::class, 'index'])->name('dashboard');

            // Skrining
            Route::get('/skrining', [\App\Http\Controllers\Bidan\SkriningController::class, 'index'])->name('skrining');

            // Detail skrining (setelah klik "Ya")
            Route::get('/skrining/{skrining}', [\App\Http\Controllers\Bidan\SkriningController::class, 'show'])
                ->name('skrining.show');

            // Update status "checked"
            Route::post('/skrining/{skrining}/mark-as-viewed', [\App\Http\Controllers\Bidan\SkriningController::class, 'markAsViewed'])
                ->name('skrining.markAsViewed');

            // Tombol "Sudah Diperiksa"
            Route::post('/skrining/{skrining}/follow-up', [\App\Http\Controllers\Bidan\SkriningController::class, 'followUp'])
                ->name('skrining.followUp');

            // ========== Profile Bidan ==========
            // Nanti temanmu tinggal buat Bidan\ProfileController + view-nya
            Route::get('/profile', [BidanProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [BidanProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile/photo', [BidanProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
        });

    // ================== RUMAH SAKIT ==================
    Route::middleware('role:rumah_sakit')
        ->prefix('rs')->as('rs.')
        ->group(function () {
            Route::get('/dashboard', [RsDashboardController::class, 'index'])->name('dashboard');
            Route::post('/dashboard/proses-nifas/{id}', [RsDashboardController::class, 'prosesPasienNifas'])->name('dashboard.proses-nifas');

            Route::get('/pasien/{id}', [RsDashboardController::class, 'showPasien'])->name('pasien.show');
            
            
            // Skrining
            Route::get('/skrining', [RsSkriningController::class, 'index'])->name('skrining.index');
            Route::get('/skrining/{id}/edit', [RsSkriningController::class, 'edit'])->name('skrining.edit'); 
            Route::get('/skrining/{id}', [RsSkriningController::class, 'show'])->name('skrining.show');
            Route::put('/skrining/{id}', [RsSkriningController::class, 'update'])->name('skrining.update');

            // Pasien Nifas
            Route::post('/pasien-nifas/{id}/anak', [RsPasienNifasController::class, 'storeAnakPasien'])->name('pasien-nifas.store-anak');
            Route::get('/pasien-nifas', [RsPasienNifasController::class, 'index'])->name('pasien-nifas.index');
            Route::get('/pasien-nifas/create', [RsPasienNifasController::class, 'create'])->name('pasien-nifas.create');
            Route::get('/pasien-nifas/download/pdf', [RsPasienNifasController::class, 'downloadPDF'])->name('pasien-nifas.download-pdf');
            Route::get('/pasien-nifas/{id}', [RsPasienNifasController::class, 'show'])->name('pasien-nifas.show');
            

            Route::post('/pasien-nifas/cek-nik', [RsPasienNifasController::class, 'cekNik'])->name('pasien-nifas.cek-nik');
            Route::post('/pasien-nifas/store', [RsPasienNifasController::class, 'store'])->name('pasien-nifas.store');
            Route::get('/pasien-nifas/{id}/detail', [RsPasienNifasController::class, 'detail'])->name('pasien-nifas.detail');
            Route::get('/rs/dashboard', function () {return view('rs.skrining.dashboard');})->name('rs.dashboard');

        });

    // ================== PASIEN ==================
    Route::middleware('role:pasien')
        ->prefix('pasien')->as('pasien.')
        ->group(function () {
            Route::get('/dashboard', [PasienDashboardController::class, 'index'])->name('dashboard');
            Route::get('/puskesmas/search', [PasienSkriningController::class, 'puskesmasSearch'])->name('puskesmas.search');

            // Pemilihan puskesmas via modal
            Route::post('/skrining/ajukan/puskesmas', [DataDiriController::class, 'storePengajuan'])
                ->name('skrining.pengajuan.store');

            // Data Diri
            Route::get('/skrining/ajukan', [DataDiriController::class, 'create'])->name('data-diri');
            Route::post('/data-diri', [DataDiriController::class, 'store'])->name('data-diri.store');

            // Riwayat Kehamilan & Persalinan (GPA)
            Route::get('/skrining/riwayat-kehamilan-gpa', [RiwayatKehamilanGPAController::class, 'riwayatKehamilanGpa'])
                ->name('riwayat-kehamilan-gpa');
            Route::post('/riwayat-kehamilan-gpa', [RiwayatKehamilanGPAController::class, 'store'])
                ->name('riwayat-kehamilan-gpa.store');

            // Kondisi Kesehatan Pasien
            Route::get('/skrining/kondisi-kesehatan-pasien', [KondisiKesehatanPasienController::class, 'kondisiKesehatanPasien'])
                ->name('kondisi-kesehatan-pasien');
            Route::post('/kondisi-kesehatan-pasien', [KondisiKesehatanPasienController::class, 'store'])
                ->name('kondisi-kesehatan-pasien.store');

            // Riwayat Penyakit Pasien
            Route::get('/skrining/riwayat-penyakit-pasien', [RiwayatPenyakitPasienController::class, 'riwayatPenyakitPasien'])
                ->name('riwayat-penyakit-pasien');
            Route::post('/riwayat-penyakit-pasien', [RiwayatPenyakitPasienController::class, 'store'])
                ->name('riwayat-penyakit-pasien.store');

            // Riwayat Penyakit Keluarga
            Route::get('/skrining/riwayat-penyakit-keluarga', [RiwayatPenyakitKeluargaController::class, 'riwayatPenyakitKeluarga'])
                ->name('riwayat-penyakit-keluarga');
            Route::post('/riwayat-penyakit-keluarga', [RiwayatPenyakitKeluargaController::class, 'store'])
                ->name('riwayat-penyakit-keluarga.store');

            // Preeklampsia
            Route::get('/skrining/preeklampsia', [PreeklampsiaController::class, 'preEklampsia'])
                ->name('preeklampsia');
            Route::post('/skrining/preeklampsia', [PreeklampsiaController::class, 'store'])
                ->name('preeklampsia.store');

            // Profile Pasien
            Route::get('/profile', [\App\Http\Controllers\Pasien\ProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [\App\Http\Controllers\Pasien\ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile/photo', [\App\Http\Controllers\Pasien\ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');

            // CRUD Skrining oleh Pasien
            Route::get('/skrining/{skrining}', [PasienSkriningController::class, 'show'])->name('skrining.show');
            Route::get('/skrining/{skrining}/edit', [PasienSkriningController::class, 'edit'])->name('skrining.edit');
            Route::put('/skrining/{skrining}', [PasienSkriningController::class, 'update'])->name('skrining.update');
            Route::delete('/skrining/{skrining}', [PasienSkriningController::class, 'destroy'])->name('skrining.destroy');
        });

    // ================== WILAYAH ==================
    Route::prefix('wilayah')->group(function () {
        Route::get('provinces', [WilayahController::class, 'provinces'])->name('wilayah.provinces');
        Route::get('regencies/{provId}', [WilayahController::class, 'regencies'])->name('wilayah.regencies');
        Route::get('districts/{kabId}', [WilayahController::class, 'districts'])->name('wilayah.districts');
        Route::get('villages/{kecId}', [WilayahController::class, 'villages'])->name('wilayah.villages');
    });
});

require __DIR__ . '/auth.php';
