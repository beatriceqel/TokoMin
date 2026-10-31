<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\FileController; 
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    // Route khusus Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
            ->name('admin.dashboard');

        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class)->except('show', 'create', 'edit');
    });

    // Route untuk Admin dan Kasir
    Route::middleware(['role:admin,kasir'])->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index'])
            ->name('transactions.index');
        Route::get('/kasir/transaksi', [TransactionController::class, 'create'])
            ->name('transactions.create');
        Route::post('/transactions', [TransactionController::class, 'store'])
            ->name('transactions.store');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])
            ->name('transactions.show');
        Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])
            ->name('transactions.receipt');

        // File Upload 
        Route::get('/upload', [FileController::class, 'showForm'])->name('file.form');
        Route::post('/upload', [FileController::class, 'upload'])->name('file.upload');

        // --- RUTE INVOICE PDF (Acara 30) ---
        Route::get('orders/{id}/invoice', [TransactionController::class, 'invoice'])->name('orders.invoice');
    });

    // Route khusus Kasir
    Route::middleware(['role:kasir'])->group(function () {
        Route::get('/kasir/dashboard', [KasirController::class, 'dashboard'])
            ->name('kasir.dashboard');
    });
});

// --- RUTE BROADCAST AUTHENTICATION (LARAVEL REVERB) ---
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

require __DIR__ . '/auth.php';