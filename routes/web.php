<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\FileController; 
use Illuminate\Support\Facades\Route;

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

        // File Upload (Acara 29)
        Route::get('/upload', [FileController::class, 'showForm'])->name('file.form');
        Route::post('/upload', [FileController::class, 'upload'])->name('file.upload');
    });

    // Route khusus Kasir
    Route::middleware(['role:kasir'])->group(function () {
        Route::get('/kasir/dashboard', [KasirController::class, 'dashboard'])
            ->name('kasir.dashboard');
    });
});

// --- RUTE INVOICE PDF (Sesuai Modul Acara 30) ---
// Diarahkan ke TransactionController dan menggunakan parameter {id}
Route::get('orders/{id}/invoice', [TransactionController::class, 'invoice'])->name('orders.invoice');

require __DIR__ . '/auth.php';