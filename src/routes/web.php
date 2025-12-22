<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController as AdminAuthenticatedSessionController;
use App\Http\Controllers\AttendanceController as UserAttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\AdminRequestController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestController;

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

    // 勤怠登録
    Route::get('/attendance', [UserAttendanceController::class, 'index'])
        ->name('attendance.index');
    Route::post('/attendance', [UserAttendanceController::class, 'store'])
        ->name('attendance.store');

    // 勤怠一覧（月次）
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    // ✅ 申請（attendance の {attendance} より上に置くのが鉄則）
    Route::get('/requests', [RequestController::class, 'index'])
        ->name('requests.index');
    Route::get('/requests/{correctionRequest}', [RequestController::class, 'show'])
    ->whereNumber('correctionRequest')
    ->name('requests.show');

    // ✅ 勤怠詳細（数字だけに限定して誤爆を防ぐ）
    Route::get('/attendance/{attendance}', [UserAttendanceController::class, 'show'])
        ->whereNumber('attendance')
        ->name('attendance.show');

    Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');
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

            Route::get('/requests', [AdminRequestController::class, 'index'])
                ->name('requests.index');

            Route::get('/requests/{request}', [AdminRequestController::class, 'show'])
                ->name('requests.show');
            Route::post('/requests/{request}/approve', [AdminRequestController::class, 'approve'])->name('requests.approve');
        });
    });
