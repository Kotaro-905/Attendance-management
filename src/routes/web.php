<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController as AdminAuthenticatedSessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;

// とりあえずトップはログインへリダイレクト
Route::get('/', function () {
    return redirect()->route('login');
});

// =======================
// 一般ユーザー用 認証
// =======================

// 会員登録
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    // 一般ログイン
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// ログアウト
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// =======================
// 一般ユーザー用 機能
// =======================

Route::middleware(['auth'])->group(function () {
    // 勤怠登録画面（出勤前／出勤中／休憩中／退勤後）
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    // 勤怠登録の各操作（出勤・休憩入・休憩戻・退勤）
    Route::post('/attendance', [AttendanceController::class, 'store'])
        ->name('attendance.store');
});

// =======================
// 管理者用 認証
// =======================

Route::prefix('admin')->name('admin.')->group(function () {

    // 管理者ログイン
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthenticatedSessionController::class, 'create'])
            ->name('login');
        Route::post('/login', [AdminAuthenticatedSessionController::class, 'store']);
    });

    // 管理者ログアウト
    Route::post('/logout', [AdminAuthenticatedSessionController::class, 'destroy'])
        ->middleware(['auth', 'admin'])
        ->name('logout');

    // ===================
    // 管理者用 機能
    // ===================
    Route::middleware(['auth', 'admin'])->group(function () {

        
    });
});