<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
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

        // Riwayat Transaksi
        Route::get('/transactions', [TransactionController::class, 'index'])
            ->name('transactions.index');

        // Halaman Tambah/Membuat Transaksi Kasir
        Route::get('/kasir/transaksi', [TransactionController::class, 'create'])
            ->name('transactions.create');

        // Simpan Transaksi
        Route::post('/transactions', [TransactionController::class, 'store'])
            ->name('transactions.store');

        // Detail Transaksi
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])
            ->name('transactions.show');

        // Cetak Struk
        Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])
            ->name('transactions.receipt');
    });

    // Route khusus Kasir
    Route::middleware(['role:kasir'])->group(function () {
        Route::get('/kasir/dashboard', [KasirController::class, 'dashboard'])
            ->name('kasir.dashboard');
    });


});

require __DIR__ . '/auth.php';