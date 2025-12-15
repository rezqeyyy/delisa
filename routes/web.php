<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Dinkes\DashboardController as DinkesDashboardController;
use App\Http\Controllers\Puskesmas\DashboardController as PuskesmasDashboardController;
use App\Http\Controllers\Bidan\DashboardController as BidanDashboardController;
use App\Http\Controllers\Rs\DashboardController as RsDashboardController;
use App\Http\Controllers\Rs\SkriningController as RsSkriningController;
use App\Http\Controllers\Rs\RujukanController as RsRujukanController;
use App\Http\Controllers\Rs\PasienNifasController as RsPasienNifasController;
use App\Http\Controllers\Pasien\DashboardController as PasienDashboardController;
use App\Http\Controllers\Pasien\SkriningController as PasienSkriningController;

use App\Http\Controllers\Dinkes\AkunBaruController;
use App\Http\Controllers\Dinkes\DataMasterController;
use App\Http\Controllers\Dinkes\PasienNifasController;
use App\Http\Controllers\Dinkes\ProfileController as DinkesProfileController;
use App\Http\Controllers\Dinkes\PasienController;

use App\Http\Controllers\Bidan\ProfileController as BidanProfileController;
use App\Http\Controllers\Bidan\PasienNifasController as BidanPasienNifasController;
use App\Http\Controllers\Bidan\AnakPasienController as BidanAnakPasienController;

use App\Http\Controllers\Rs\ProfileController as RsProfileController;


use App\Http\Controllers\WilayahController;
use App\Http\Controllers\Pasien\Skrining\DataDiriController;
use App\Http\Controllers\Pasien\Skrining\RiwayatKehamilanGPAController;
use App\Http\Controllers\Pasien\Skrining\KondisiKesehatanPasienController;
use App\Http\Controllers\Pasien\Skrining\RiwayatPenyakitPasienController;
use App\Http\Controllers\Pasien\Skrining\RiwayatPenyakitKeluargaController;
use App\Http\Controllers\Pasien\Skrining\PreeklampsiaController;
use \App\Http\Controllers\Pasien\ProfileController as PasienProfileController;

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
            'rumah_sakit', 'rs' => redirect()->route('rs.dashboard'),
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
            Route::get('/dashboard/export-pe', [DinkesDashboardController::class, 'exportPe'])
                ->name('dashboard.pe-export');
            Route::delete('/dinkes/dashboard/pasien-preeklampsia/{skrining}', [DinkesDashboardController::class, 'destroyPe'])
                ->name('dashboard.pe-destroy');


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
            Route::get('/pasien-nifas/download/xlsx', [PasienNifasController::class, 'export'])->name('pasien-nifas.export');
            Route::get('/pasien-nifas/{nifasId}', [PasienNifasController::class, 'show'])->name('pasien-nifas.show');


            // Profile Dinkes
            Route::get('/profile', [DinkesProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [DinkesProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile/photo', [DinkesProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
        });

    // ================== PUSKESMAS ==================
    Route::middleware('role:puskesmas')
    ->prefix('puskesmas')->as('puskesmas.')
    ->group(function () {
        
        // ========== DASHBOARD & PROFILE ==========
        Route::get('dashboard', [PuskesmasDashboardController::class, 'index'])->name('dashboard');
        Route::get('profile/edit', [\App\Http\Controllers\Puskesmas\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [\App\Http\Controllers\Puskesmas\ProfileController::class, 'update'])->name('profile.update');
        Route::delete('profile/photo', [\App\Http\Controllers\Puskesmas\ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
        
        // ========== SKRINING ==========
        Route::prefix('skrining')->name('skrining.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Puskesmas\SkriningController::class, 'index'])->name('index');
            Route::get('{skrining}', [\App\Http\Controllers\Puskesmas\SkriningController::class, 'show'])->name('show');
            Route::patch('{skrining}/verify', [\App\Http\Controllers\Puskesmas\SkriningController::class, 'verify'])->name('verify');
            Route::get('export/excel', [\App\Http\Controllers\Puskesmas\SkriningController::class, 'exportExcel'])->name('export.excel');
            Route::get('export/pdf', [\App\Http\Controllers\Puskesmas\SkriningController::class, 'exportPdf'])->name('export.pdf');
        });
        
        // ========== RUJUKAN ==========
        Route::resource('rujukan', \App\Http\Controllers\Puskesmas\RujukanController::class)->except(['create', 'edit', 'destroy']);
        Route::put('rujukan/{id}/status', [\App\Http\Controllers\Puskesmas\RujukanController::class, 'updateStatus'])->name('rujukan.status.update');
        Route::get('rujukan/rs/search', [\App\Http\Controllers\Puskesmas\RujukanController::class, 'searchRS'])->name('rujukan.rs.search');
        Route::post('skrining/{skrining}/rujuk', [\App\Http\Controllers\Puskesmas\RujukanController::class, 'ajukanRujukan'])->name('skrining.rujuk');
        
        // ========== PASIEN NIFAS ==========
        Route::prefix('pasien-nifas')->name('pasien-nifas.')->group(function () {
            // Index dengan filter
            Route::get('/', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'index'])->name('index');
            
            // ========== ROUTE UNIVERSAL DENGAN PARAMETER {type} ==========
            // Route dengan parameter {type} (rs/bidan)
            Route::get('/{type}/{id}', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'show'])->name('show');
            Route::get('/{type}/{id}/kf/{jenisKf}', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'formCatatKf'])->name('form-kf');
            Route::post('/{type}/{id}/kf/{jenisKf}', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'catatKf'])->name('catat-kf');
            Route::get('/{type}/{id}/kf/{jenisKf}/pdf', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'downloadKfPdf'])->name('kf.pdf');
            Route::get('/{type}/{id}/all-kf/pdf', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'downloadAllKfPdf'])->name('all-kf.pdf');
            
            // ========== ROUTE LAMA UNTUK BACKWARD COMPATIBILITY ==========
            // Route tanpa parameter {type} (default ke RS)
            Route::get('/{id}', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'showRs'])->name('show.legacy');
            Route::get('/{id}/kf/{jenisKf}', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'formCatatKfLegacy'])->name('form-kf.legacy');
            Route::post('/{id}/kf/{jenisKf}', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'catatKfLegacy'])->name('catat-kf.legacy');
            Route::get('/{id}/kf/{jenisKf}/pdf', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'downloadKfPdfLegacy'])->name('kf.pdf.legacy');
            Route::get('/{id}/all-kf/pdf', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'downloadAllKfPdfLegacy'])->name('all-kf.pdf.legacy');
        });
        
        // ========== WILAYAH & LAINNYA ==========
        Route::get('wilayah', [\App\Http\Controllers\WilayahController::class, 'index'])->name('wilayah.index');
    });// ================== PUSKESMAS ==================
    Route::middleware('role:puskesmas')
    ->prefix('puskesmas')->as('puskesmas.')
    ->group(function () {
        
        // ========== DASHBOARD & PROFILE ==========
        Route::get('dashboard', [PuskesmasDashboardController::class, 'index'])->name('dashboard');
        Route::get('profile/edit', [\App\Http\Controllers\Puskesmas\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [\App\Http\Controllers\Puskesmas\ProfileController::class, 'update'])->name('profile.update');
        Route::delete('profile/photo', [\App\Http\Controllers\Puskesmas\ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
        
        // ========== SKRINING ==========
        Route::prefix('skrining')->name('skrining.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Puskesmas\SkriningController::class, 'index'])->name('index');
            Route::get('{skrining}', [\App\Http\Controllers\Puskesmas\SkriningController::class, 'show'])->name('show');
            Route::patch('{skrining}/verify', [\App\Http\Controllers\Puskesmas\SkriningController::class, 'verify'])->name('verify');
            Route::get('export/excel', [\App\Http\Controllers\Puskesmas\SkriningController::class, 'exportExcel'])->name('export.excel');
            Route::get('export/pdf', [\App\Http\Controllers\Puskesmas\SkriningController::class, 'exportPdf'])->name('export.pdf');
        });
        
        // ========== RUJUKAN ==========
        Route::resource('rujukan', \App\Http\Controllers\Puskesmas\RujukanController::class)->except(['create', 'edit', 'destroy']);
        Route::put('rujukan/{id}/status', [\App\Http\Controllers\Puskesmas\RujukanController::class, 'updateStatus'])->name('rujukan.status.update');
        Route::get('rujukan/rs/search', [\App\Http\Controllers\Puskesmas\RujukanController::class, 'searchRS'])->name('rujukan.rs.search');
        Route::post('skrining/{skrining}/rujuk', [\App\Http\Controllers\Puskesmas\RujukanController::class, 'ajukanRujukan'])->name('skrining.rujuk');
        
        // ========== PASIEN NIFAS ==========
        Route::prefix('pasien-nifas')->name('pasien-nifas.')->group(function () {
            // Index dengan filter
            Route::get('/', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'index'])->name('index');
            
            // ========== ROUTE UNIVERSAL DENGAN PARAMETER {type} ==========
            // Route dengan parameter {type} (rs/bidan)
            Route::get('/{type}/{id}', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'show'])->name('show');
            Route::get('/{type}/{id}/kf/{jenisKf}', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'formCatatKf'])->name('form-kf');
            Route::post('/{type}/{id}/kf/{jenisKf}', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'catatKf'])->name('catat-kf');
            Route::get('/{type}/{id}/kf/{jenisKf}/pdf', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'downloadKfPdf'])->name('kf.pdf');
            Route::get('/{type}/{id}/all-kf/pdf', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'downloadAllKfPdf'])->name('all-kf.pdf');
            
            // ========== ROUTE LAMA UNTUK BACKWARD COMPATIBILITY ==========
            // Route tanpa parameter {type} (default ke RS)
            Route::get('/{id}', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'showRs'])->name('show.legacy');
            Route::get('/{id}/kf/{jenisKf}', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'formCatatKfLegacy'])->name('form-kf.legacy');
            Route::post('/{id}/kf/{jenisKf}', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'catatKfLegacy'])->name('catat-kf.legacy');
            Route::get('/{id}/kf/{jenisKf}/pdf', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'downloadKfPdfLegacy'])->name('kf.pdf.legacy');
            Route::get('/{id}/all-kf/pdf', [\App\Http\Controllers\Puskesmas\PasienNifasController::class, 'downloadAllKfPdfLegacy'])->name('all-kf.pdf.legacy');
        });
        
        // ========== WILAYAH & LAINNYA ==========
        Route::get('wilayah', [\App\Http\Controllers\WilayahController::class, 'index'])->name('wilayah.index');
    });
    
    // ================== BIDAN ==================
    Route::middleware('role:bidan')
        ->prefix('bidan')->as('bidan.')
        ->group(function () {
            // Dashboard
            Route::get('/dashboard', [BidanDashboardController::class, 'index'])->name('dashboard');

            // Skrining (dengan ekspor Excel & PDF)
            Route::get('/skrining', [\App\Http\Controllers\Bidan\SkriningController::class, 'index'])->name('skrining');
            Route::get('/skrining/export/excel', [\App\Http\Controllers\Bidan\SkriningController::class, 'exportExcel'])->name('export.excel');
            Route::get('/skrining/export/pdf', [\App\Http\Controllers\Bidan\SkriningController::class, 'exportPDF'])->name('export.pdf');

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

            Route::get('/pasien-nifas', [BidanPasienNifasController::class, 'index'])->name('pasien-nifas');
            Route::get('/pasien-nifas/create', [BidanPasienNifasController::class, 'create'])->name('pasien-nifas.create');
            Route::get('/pasien-nifas/{id}/anak', [BidanAnakPasienController::class, 'create'])->name('pasien-nifas.anak.create');
            Route::post('/pasien-nifas/{id}/anak', [BidanAnakPasienController::class, 'store'])->name('pasien-nifas.store-anak');
            Route::get('/pasien-nifas/{id}/anak/{anakId}/edit', [BidanAnakPasienController::class, 'edit'])->name('pasien-nifas.anak.edit');
            Route::put('/pasien-nifas/{id}/anak/{anakId}', [BidanAnakPasienController::class, 'update'])->name('pasien-nifas.anak.update');
            Route::delete('/pasien-nifas/{id}/anak/{anakId}', [BidanAnakPasienController::class, 'destroy'])->name('pasien-nifas.anak.destroy');
            Route::get('/pasien-nifas/{id}/show', [BidanPasienNifasController::class, 'detail'])->name('pasien-nifas.show');
            Route::get('/pasien-nifas/{id}/detail', [BidanPasienNifasController::class, 'detail'])->name('pasien-nifas.detail');
            Route::get('/pasien-nifas/{id}/anak/{anakId}/kf/{jenisKf}', [BidanPasienNifasController::class, 'formKfAnak'])->name('pasien-nifas.kf-anak.form');
            Route::post('/pasien-nifas/{id}/anak/{anakId}/kf/{jenisKf}', [BidanPasienNifasController::class, 'catatKfAnak'])->name('pasien-nifas.kf-anak.catat');

            Route::delete('/pasien-nifas/{id}', [BidanPasienNifasController::class, 'destroy'])->name('pasien-nifas.destroy');
            Route::post('/pasien-nifas/cek-nik', [BidanPasienNifasController::class, 'cekNik'])->name('pasien-nifas.cek-nik');
            Route::post('/pasien-nifas/store', [BidanPasienNifasController::class, 'store'])->name('pasien-nifas.store');

            // Rujukan nifas masuk dari RS (tujuan puskesmas = puskesmas bidan)
            Route::get('/rujukan-masuk', [\App\Http\Controllers\Bidan\PasienNifasController::class, 'rujukanMasuk'])
                ->name('rujukan-masuk');

            // Terima rujukan (buat entry di pasien_nifas_bidan)
            Route::post('/rujukan-masuk/{nifasRs}/terima', [\App\Http\Controllers\Bidan\PasienNifasController::class, 'terimaRujukan'])
                ->name('rujukan-masuk.terima');
        });

    // ================== RUMAH SAKIT ==================
    // Izinkan dua nama role: "rumah_sakit" dan "rs"
    Route::middleware('role:rumah_sakit,rs')
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
            Route::get('/skrining/{id}/export-pdf', [RsSkriningController::class, 'exportPdf'])->name('skrining.exportPdf');


            // Penerimaan Rujukan
            Route::get('/penerimaan-rujukan', [RsRujukanController::class, 'index'])->name('penerimaan-rujukan.index');
            Route::post('/cek-nik', [PasienNifasController::class, 'cekNik'])->name('cek-nik');
            Route::post('/penerimaan-rujukan/{id}/accept', [RsRujukanController::class, 'accept'])->name('penerimaan-rujukan.accept');
            Route::post('/penerimaan-rujukan/{id}/reject', [RsRujukanController::class, 'reject'])->name('penerimaan-rujukan.reject');


            // Pasien Nifas
            Route::post('/cek-nik', [PasienNifasController::class, 'cekNik'])->name('cek-nik');
            Route::post('/pasien-nifas/{id}/anak', [RsPasienNifasController::class, 'storeAnakPasien'])->name('pasien-nifas.store-anak');
            Route::get('/pasien-nifas', [RsPasienNifasController::class, 'index'])->name('pasien-nifas.index');
            Route::get('/pasien-nifas/create', [RsPasienNifasController::class, 'create'])->name('pasien-nifas.create');
            Route::get('/pasien-nifas/download/pdf', [RsPasienNifasController::class, 'downloadPDF'])->name('pasien-nifas.download-pdf');
            Route::get('/pasien-nifas/{id}', [RsPasienNifasController::class, 'show'])->name('pasien-nifas.show');
            Route::get('/pasien-nifas/{id}/download-pdf', [RsPasienNifasController::class, 'downloadSinglePDF'])->name('pasien-nifas.download-single-pdf');
            Route::get('/pasien-nifas/{id}/detail', [RsPasienNifasController::class, 'detail'])->name('pasien-nifas.detail');
            Route::get('/pasien-nifas/search-puskesmas', [App\Http\Controllers\Rs\PasienNifasController::class, 'searchPuskesmas'])->name('pasien-nifas.search-puskesmas');


            Route::post('/pasien-nifas/cek-nik', [RsPasienNifasController::class, 'cekNik'])->name('pasien-nifas.cek-nik');
            Route::post('/pasien-nifas/store', [RsPasienNifasController::class, 'store'])->name('pasien-nifas.store');
            Route::get('/pasien-nifas/{id}/detail', [RsPasienNifasController::class, 'detail'])->name('pasien-nifas.detail');

            // Profile Rs
            Route::get('/profile', [RsProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [RsProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile/photo', [RsProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
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
            Route::get('/profile', [PasienProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [PasienProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile/photo', [PasienProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');

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
