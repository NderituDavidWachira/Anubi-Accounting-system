<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminDashboardController;

// Authentication
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.post');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Root redirect based on role
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin() 
            ? redirect()->route('admin.dashboard') 
            : redirect()->route('vouchers.index');
    }
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    // Common Voucher operations
    Route::resource('vouchers', VoucherController::class)->only(['index', 'create', 'store', 'show']);

    // Admin-Only Routes
    Route::middleware(['can:isAdmin'])->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('admin/approvals', [AdminDashboardController::class, 'approvalsQueue'])->name('admin.approvals');
        Route::get('admin/accounts', [AdminDashboardController::class, 'chartOfAccounts'])->name('admin.accounts');

        Route::post('vouchers/{voucher}/approve', [VoucherController::class, 'approve'])->name('vouchers.approve');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('general-ledger', [ReportController::class, 'generalLedger'])->name('general-ledger');
            Route::get('trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
        });
    });

});