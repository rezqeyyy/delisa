<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RoleRegistrationController;
use Illuminate\Support\Facades\DB;

// ======================================================
// ===============  GUEST ONLY ROUTES  ==================
// ======================================================
Route::middleware('guest')->group(function () {

    // REGISTER DEFAULT BAWAAN
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    // PASIEN
    Route::get('register-pasien', function () {
        return view('auth.register-pasien');
    })->name('pasien.register');

    Route::post('register-pasien', [RoleRegistrationController::class, 'storePasien'])
        ->name('pasien.register.store');


    // ======================================================
    // ===========  PUSKESMAS (DIPERBAIKI)  =================
    // ======================================================

    // GET: tampilkan form register puskesmas → wajib pakai controller
    Route::get('register-puskesmas', [RoleRegistrationController::class, 'showPuskesmasRegisterForm'])
        ->name('puskesmas.register');

    // POST: simpan pengajuan akun puskesmas
    Route::post('register-puskesmas', [RoleRegistrationController::class, 'storePuskesmas'])
        ->name('puskesmas.register.store');


    // ======================================================
    // ===============  RUMAH SAKIT  ========================
    // ======================================================
    Route::get('register-rs', function () {

        $c = app(\App\Http\Controllers\Auth\RoleRegistrationController::class);

        return view('auth.register-rs', [
            'rsKecamatanOptions' => $c->depokKecamatanOptions(),
            'rsKelurahanByKecamatan' => $c->depokKelurahanByKecamatan(),
        ]);
    })->name('rs.register');


    Route::post('register-rs', [RoleRegistrationController::class, 'storeRs'])
        ->name('rs.register.store');


    // ======================================================
    // ===============  BIDAN MANDIRI  ======================
    // ======================================================
    Route::get('register-bidanMandiri', function () {

        $puskesmasList = DB::table('puskesmas')
            ->join('users', 'users.id', '=', 'puskesmas.user_id')
            ->where('users.status', true)
            ->orderBy('puskesmas.nama_puskesmas')
            ->select('puskesmas.id', 'puskesmas.nama_puskesmas')
            ->get();

        return view('auth.register-bidanMandiri', compact('puskesmasList'));
    })->name('bidanMandiri.register');

    Route::post('register-bidanMandiri', [RoleRegistrationController::class, 'storeBidan'])
        ->name('bidanMandiri.register.store');


    // LOGIN
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // LOGIN PASIEN
    Route::get('login-pasien', [AuthenticatedSessionController::class, 'createPasien'])
        ->name('pasien.login');

    Route::post('login-pasien', [AuthenticatedSessionController::class, 'storePasien'])
        ->name('pasien.login.store');


    // PASSWORD RESET
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});


// ======================================================
// ===============  AUTH-ONLY ROUTES  ====================
// ======================================================
Route::middleware('auth')->group(function () {

    // VERIFIKASI EMAIL
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post(
        'email/verification-notification',
        [EmailVerificationNotificationController::class, 'store']
    )
        ->middleware('throttle:6,1')
        ->name('verification.send');


    // CONFIRM PASSWORD
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);


    // UPDATE PASSWORD
    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');


    // LOGOUT
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    Route::post('logout-pasien', [AuthenticatedSessionController::class, 'destroyPasien'])
        ->name('logout.pasien');
});
