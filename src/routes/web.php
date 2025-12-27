<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController as AdminAuthenticatedSessionController;

use App\Http\Controllers\AttendanceController as UserAttendanceController;
use App\Http\Controllers\AttendanceController; // 月次一覧(list)で使っているなら残す

use App\Http\Controllers\RequestController; // 一般ユーザーの申請
use App\Http\Controllers\Admin\AdminRequestController; // 管理者の申請

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;

/*
|--------------------------------------------------------------------------
| トップ / home
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('admin.login'));
Route::get('/home', fn () => redirect()->route('admin.login'));

/*
|--------------------------------------------------------------------------
| 一般ユーザー 認証
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // 会員登録
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    // ログイン
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// ログアウト（一般）
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| 一般ユーザー 機能
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // 出勤登録（打刻）
    Route::get('/attendance', [UserAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [UserAttendanceController::class, 'store'])->name('attendance.store');

    // 勤怠一覧（月次）
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    // 勤怠詳細（設計書：/attendance/detail/{id}）
    Route::get('/attendance/detail/{attendance}', [UserAttendanceController::class, 'show'])
        ->whereNumber('attendance')
        ->name('attendance.show');

    // 申請一覧（設計書：/stamp_correction_request/list）
    Route::get('/stamp_correction_request/list', [RequestController::class, 'index'])
        ->name('requests.index');

    // 申請詳細（設計書に行が無いけど、機能として必要ならこのまま）
    Route::get('/stamp_correction_request/{correctionRequest}', [RequestController::class, 'show'])
        ->whereNumber('correctionRequest')
        ->name('requests.show');

    // 修正申請 作成（勤怠詳細の「修正」）
    Route::post('/stamp_correction_request', [RequestController::class, 'store'])
        ->name('requests.store');
});

/*
|--------------------------------------------------------------------------
| 管理者 認証 ＋ 機能
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    // 管理者ログイン（guest）
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthenticatedSessionController::class, 'store']);
    });

    // 管理者ログアウト
    Route::post('/logout', [AdminAuthenticatedSessionController::class, 'destroy'])
        ->middleware(['auth', 'admin'])
        ->name('logout');

    // 管理者機能（auth+admin）
    Route::middleware(['auth', 'admin'])->group(function () {

        // 勤怠一覧（管理者）
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
            ->name('attendance.index');

        // 勤怠詳細（管理者）
        Route::get('/attendance/{attendance}', [AdminAttendanceController::class, 'show'])
            ->whereNumber('attendance')
            ->name('attendance.show');

        // 勤怠更新（管理者）
        Route::put('/attendance/{attendance}', [AdminAttendanceController::class, 'update'])
            ->whereNumber('attendance')
            ->name('attendance.update');

        // スタッフ一覧（設計書：/admin/staff/list）
        Route::get('/staff/list', [StaffController::class, 'index'])
            ->name('staff.index');

        // スタッフ別勤怠一覧（設計書：/admin/attendance/staff/{id}）
        Route::get('/attendance/staff/{user}', [StaffController::class, 'attendance'])
            ->whereNumber('user')
            ->name('staff.attendance');

        // （任意）CSV出力：設計書に無いが実装するなら自然な場所
        Route::get('/attendance/staff/{user}/csv', [StaffController::class, 'exportCsv'])
            ->whereNumber('user')
            ->name('staff.attendance.csv');

        // 申請一覧（管理者）※設計書の意図に合わせて admin配下に寄せる
        Route::get('/stamp_correction_request/list', [AdminRequestController::class, 'index'])
            ->name('requests.index');

        // 修正申請承認画面（設計書：/stamp_correction_request/approve/{id} を admin配下で）
        Route::get('/stamp_correction_request/approve/{request}', [AdminRequestController::class, 'show'])
            ->whereNumber('request')
            ->name('requests.show');

        // 承認処理（設計書が GET/POST なので同URLにPOST）
        Route::post('/stamp_correction_request/approve/{request}', [AdminRequestController::class, 'approve'])
            ->whereNumber('request')
            ->name('requests.approve');
    });
});
