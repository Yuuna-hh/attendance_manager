<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionRequestController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminCorrectionRequestController;

Route::get('/', fn() => redirect('/login'));

// 認証
Route::get('/register', [AuthController::class, 'showRegister'])
    ->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])
    ->name('admin_login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);

// メール認証
Route::get('/email/verify', function (Request $request) {
    if ($request->user() && !$request->user()->hasVerifiedEmail()) {
        $request->user()->sendEmailVerificationNotification();
    }

    return view('general.email-verify');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('attendance_index');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// 認証ミドルウェア
Route::middleware(['auth'])->group(function () {

    // 一般ユーザー
    Route::middleware(['role:general', 'verified'])->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index'])
            ->name('attendance_index');
        Route::post('/attendance', [AttendanceController::class, 'store']);

        Route::get('/attendance/list', [AttendanceController::class, 'list'])
            ->name('attendance_list');

        Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])
            ->name('attendance_detail');
        Route::post('/attendance/detail/{id}', [AttendanceController::class, 'update'])
            ->name('attendance_update');

        Route::get('/stamp_correction_request/detail/{attendance_correct_request_id}', [AttendanceCorrectionRequestController::class, 'show'])
            ->name('correction_request_detail');
    });

    // 管理者
    Route::middleware(['role:admin'])->group(function () {

        Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'list'])
            ->name('admin_attendance_list');

        Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'detail'])
            ->name('admin_attendance_detail');
        Route::post('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])
            ->name('admin_attendance_update');

        Route::get('/admin/staff/list', [AdminStaffController::class, 'list'])
            ->name('admin_staff_list');

        Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'staffList'])
            ->name('admin_staff_attendance_list');

        Route::get('/admin/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportCsv'])
            ->name('admin_staff_attendance_csv');
    
        Route::get('/stamp_correction_request/list', [AdminCorrectionRequestController::class, 'index'])
            ->name('correction_request_list');

        Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminCorrectionRequestController::class, 'show'])
            ->name('admin_correction_request_approve');

        Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminCorrectionRequestController::class, 'update'])
            ->name('admin_correction_request_approve_update');
    });

        // 共通URL
    Route::get('/stamp_correction_request/list', [AttendanceCorrectionRequestController::class, 'index'])
        ->name('correction_request_list');
});

