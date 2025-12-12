<?php

use Illuminate\Support\Facades\Route;

// 一般ユーザー認証
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// 管理者認証
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController as AdminAuthenticatedSessionController;

// 一般ユーザー用 勤怠
use App\Http\Controllers\AttendanceController as UserAttendanceController;

// 一般ユーザー用 打刻修正申請（使うなら）
use App\Http\Controllers\StampCorrectionRequestController;

// 管理者用 勤怠
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;

// 管理者用 スタッフ管理 / 打刻修正申請（使うなら）
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;

/*
|--------------------------------------------------------------------------
| トップ / home
|--------------------------------------------------------------------------
|
| 今回は 「/」 と 「/home」 はどちらも管理者ログイン画面へ飛ばす仕様のまま。
| 一般ユーザーは /attendance に直接アクセスしてもらう想定。
|
*/

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::get('/home', function () {
    return redirect()->route('admin.login');
});

/*
|--------------------------------------------------------------------------
| 一般ユーザー 認証
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // 会員登録
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    // 一般ログイン
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// 一般ログアウト
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| 一般ユーザー 機能
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // 勤怠登録画面（出勤前／出勤中／休憩中／退勤後）
    Route::get('/attendance', [UserAttendanceController::class, 'index'])
        ->name('attendance.index');

    // 勤怠登録の各操作（出勤・休憩入・休憩戻・退勤）
    Route::post('/attendance', [UserAttendanceController::class, 'store'])
        ->name('attendance.store');

    // 打刻修正申請を使うならここにルートを追加
    // Route::post('/stamp-corrections', [StampCorrectionRequestController::class, 'store'])
    //     ->name('stamp_corrections.store');
});

/*
|--------------------------------------------------------------------------
| 管理者 認証 ＋ 機能
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {

        // ---------------- 管理者ログイン（guest のみ） ----------------
        Route::middleware('guest')->group(function () {
            Route::get('/login', [AdminAuthenticatedSessionController::class, 'create'])
                ->name('login');
            Route::post('/login', [AdminAuthenticatedSessionController::class, 'store']);
        });

        // ---------------- 管理者ログアウト ----------------
        Route::post('/logout', [AdminAuthenticatedSessionController::class, 'destroy'])
            ->middleware(['auth', 'admin'])
            ->name('logout');

        // ---------------- 管理者機能（auth + admin） ----------------
        Route::middleware(['auth', 'admin'])->group(function () {

            // 勤怠一覧（管理者）
            // URL：/admin/attendance/list
            Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
                ->name('attendance.index');

            // 勤怠詳細画面（管理者）
            // URL：/admin/attendance/{attendance}
            Route::get('/attendance/{attendance}', [AdminAttendanceController::class, 'show'])
                ->name('attendance.show');

            // 勤怠更新（管理者）
            Route::put('/attendance/{attendance}', [AdminAttendanceController::class, 'update'])
                ->name('attendance.update');

            // ▼ スタッフ一覧（これが「admin.staff.index」になります）
            Route::get('/staff', [StaffController::class, 'index'])
                ->name('staff.index');

            // スタッフ月次勤怠一覧（新規追加）
           Route::get('/staff/{user}/attendance', [\App\Http\Controllers\Admin\StaffController::class, 'attendance'])
        ->name('staff.attendance');

            // 打刻修正申請の管理画面を使うならここに追加
            // Route::get('/stamp-corrections', [AdminStampCorrectionRequestController::class, 'index'])
            //     ->name('stamp_corrections.index');
        });
    });
