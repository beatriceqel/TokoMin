@extends('layouts.app')

@section('content')

<style>
    .kasir-dashboard {
        min-height: calc(100vh - 70px);
        background: #f5f7f8;
        padding: 35px 25px 50px;
    }

    .dashboard-container {
        max-width: 1250px;
        margin: 0 auto;
    }

    /* =========================
       HEADER
    ========================= */

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 25px;
    }

    .dashboard-title {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #111827;
    }

    .dashboard-subtitle {
        margin: 7px 0 0;
        font-size: 14px;
        color: #6b7280;
    }

    .dashboard-date {
        padding: 10px 15px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }


    /* =========================
       STATISTICS
    ========================= */

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .stat-card {
        position: relative;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 15px;
        padding: 22px;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .stat-card::after {
        content: "";
        position: absolute;
        width: 90px;
        height: 90px;
        right: -35px;
        top: -35px;
        border-radius: 50%;
        background: #e9f7ef;
    }

    .stat-icon {
        width: 43px;
        height: 43px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: #e9f7ef;
        color: #16815b;
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 17px;
    }

    .stat-label {
        margin: 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
    }

    .stat-value {
        margin: 7px 0 0;
        color: #111827;
        font-size: 25px;
        font-weight: 800;
    }

    .stat-description {
        margin: 6px 0 0;
        color: #94a3b8;
        font-size: 12px;
    }


    /* =========================
       QUICK ACTION
    ========================= */

    .section-title {
        margin: 0 0 14px;
        font-size: 19px;
        font-weight: 700;
        color: #111827;
    }

    .quick-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 25px;
    }

    .quick-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 20px;
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
        transition: 0.2s ease;
    }

    .quick-card:hover {
        border-color: #bfdbfe;
        box-shadow: 0 7px 20px rgba(37, 99, 235, 0.10);
        transform: translateY(-2px);
    }

    .quick-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .quick-icon {
        width: 45px;
        height: 45px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: #e9f7ef;
        color: #16815b;
        font-size: 16px;
        font-weight: 800;
    }

    .quick-title {
        margin: 0;
        color: #111827;
        font-size: 15px;
        font-weight: 700;
    }

    .quick-description {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
    }

    .quick-arrow {
        color: #94a3b8;
        font-size: 22px;
        font-weight: 400;
    }


    /* =========================
       TWO COLUMN CONTENT
    ========================= */

    .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(300px, 1fr);
        gap: 20px;
    }

    .dashboard-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .card-header {
        padding: 20px 22px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-title {
        margin: 0;
        color: #111827;
        font-size: 17px;
        font-weight: 700;
    }

    .card-description {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 12px;
    }


    /* =========================
       TRANSACTION LIST
    ========================= */

    .transaction-list {
        padding: 5px 22px;
    }

    .transaction-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 16px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .transaction-item:last-child {
        border-bottom: none;
    }

    .transaction-left {
        min-width: 0;
    }

    .transaction-number {
        margin: 0;
        color: #16815b;
        font-size: 13px;
        font-weight: 700;
    }

    .transaction-cashier {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
    }

    .transaction-right {
        text-align: right;
        flex-shrink: 0;
    }

    .transaction-total {
        margin: 0;
        color: #111827;
        font-size: 13px;
        font-weight: 700;
    }

    .transaction-status {
        display: inline-flex;
        margin-top: 5px;
        padding: 4px 8px;
        border-radius: 999px;
        background: #ecfdf5;
        color: #047857;
        font-size: 10px;
        font-weight: 700;
    }

    .transaction-empty {
        padding: 40px 20px;
        text-align: center;
        color: #94a3b8;
        font-size: 13px;
    }

    .view-all {
        display: block;
        padding: 14px 22px;
        border-top: 1px solid #e5e7eb;
        color: #16815b;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        text-align: center;
    }

    .view-all:hover {
        background: #f8fafc;
    }


    /* =========================
       LOW STOCK
    ========================= */

    .stock-list {
        padding: 5px 22px;
    }

    .stock-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .stock-item:last-child {
        border-bottom: none;
    }

    .stock-name {
        margin: 0;
        color: #374151;
        font-size: 13px;
        font-weight: 700;
    }

    .stock-category {
        margin: 4px 0 0;
        color: #94a3b8;
        font-size: 11px;
    }

    .stock-badge {
        display: inline-flex;
        min-width: 38px;
        justify-content: center;
        padding: 5px 8px;
        border-radius: 8px;
        background: #fef2f2;
        color: #dc2626;
        font-size: 11px;
        font-weight: 800;
    }

    .stock-safe {
        padding: 35px 20px;
        text-align: center;
        color: #047857;
        font-size: 13px;
        font-weight: 600;
    }


    /* =========================
       RESPONSIVE
    ========================= */

    @media (max-width: 900px) {

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
        }

    }


    @media (max-width: 650px) {

        .kasir-dashboard {
            padding: 25px 15px 40px;
        }

        .dashboard-header {
            flex-direction: column;
        }

        .dashboard-date {
            width: 100%;
            box-sizing: border-box;
        }

        .dashboard-title {
            font-size: 24px;
        }

        .quick-grid {
            grid-template-columns: 1fr;
        }

        .transaction-item {
            align-items: flex-start;
        }

    }
</style>


<div class="kasir-dashboard">

    <div class="dashboard-container">

        {{-- =========================
             HEADER
        ========================== --}}

        <div class="dashboard-header">

            <div>

                <h1 class="dashboard-title">
                    Dashboard Kasir
                </h1>

                <p class="dashboard-subtitle">
                    Ringkasan aktivitas transaksi penjualan hari ini.
                </p>

            </div>

            <div class="dashboard-date">
                {{ now()->translatedFormat('l, d F Y') }}
            </div>

        </div>


        {{-- =========================
             STATISTICS
        ========================== --}}

        <div class="stats-grid">

            <div class="stat-card">

                <div class="stat-icon">
                    TRX
                </div>

                <p class="stat-label">
                    Transaksi Hari Ini
                </p>

                <p class="stat-value">
                    {{ $todayTransactions }}
                </p>

                <p class="stat-description">
                    Transaksi selesai
                </p>

            </div>


            <div class="stat-card">

                <div class="stat-icon">
                    Rp
                </div>

                <p class="stat-label">
                    Penjualan Hari Ini
                </p>

                <p class="stat-value">
                    Rp {{ number_format((float) $todaySales, 0, ',', '.') }}
                </p>

                <p class="stat-description">
                    Total penjualan selesai
                </p>

            </div>


            <div class="stat-card">

                <div class="stat-icon">
                    PRD
                </div>

                <p class="stat-label">
                    Produk Aktif
                </p>

                <p class="stat-value">
                    {{ $totalProducts }}
                </p>

                <p class="stat-description">
                    Produk tersedia di toko
                </p>

            </div>

        </div>


        {{-- =========================
             AKSI CEPAT
        ========================== --}}

        <h2 class="section-title">
            Aksi Cepat
        </h2>

        <div class="quick-grid">

            <a
                href="{{ route('transactions.create') }}"
                class="quick-card"
            >

                <div class="quick-left">

                    <div class="quick-icon">
                        +
                    </div>

                    <div>

                        <p class="quick-title">
                            Transaksi Baru
                        </p>

                        <p class="quick-description">
                            Mulai transaksi penjualan baru.
                        </p>

                    </div>

                </div>

                <div class="quick-arrow">
                    →
                </div>

            </a>


            <a
                href="{{ route('transactions.index') }}"
                class="quick-card"
            >

                <div class="quick-left">

                    <div class="quick-icon">
                        TRX
                    </div>

                    <div>

                        <p class="quick-title">
                            Riwayat Transaksi
                        </p>

                        <p class="quick-description">
                            Lihat transaksi penjualan sebelumnya.
                        </p>

                    </div>

                </div>

                <div class="quick-arrow">
                    →
                </div>

            </a>

        </div>


        {{-- =========================
             DATA TERBARU
        ========================== --}}

        <div class="dashboard-grid">

            {{-- TRANSAKSI TERBARU --}}
            <div class="dashboard-card">

                <div class="card-header">

                    <h2 class="card-title">
                        Transaksi Terbaru
                    </h2>

                    <p class="card-description">
                        Lima transaksi terakhir yang tercatat.
                    </p>

                </div>


                <div class="transaction-list">

                    @forelse($latestTransactions as $transaction)

                        <div class="transaction-item">

                            <div class="transaction-left">

                                <p class="transaction-number">
                                    {{ $transaction->transaction_number }}
                                </p>

                                <p class="transaction-cashier">
                                    Kasir:
                                    {{ $transaction->user->name ?? 'Kasir' }}
                                </p>

                            </div>


                            <div class="transaction-right">

                                <p class="transaction-total">
                                    Rp {{ number_format((float) $transaction->grand_total, 0, ',', '.') }}
                                </p>

                                <span class="transaction-status">
                                    {{ ucfirst($transaction->status) }}
                                </span>

                            </div>

                        </div>

                    @empty

                        <div class="transaction-empty">
                            Belum ada transaksi.
                        </div>

                    @endforelse

                </div>


                @if($latestTransactions->count() > 0)

                    <a
                        href="{{ route('transactions.index') }}"
                        class="view-all"
                    >
                        Lihat Semua Transaksi
                    </a>

                @endif

            </div>


            {{-- STOK MENIPIS --}}
            <div class="dashboard-card">

                <div class="card-header">

                    <h2 class="card-title">
                        Stok Menipis
                    </h2>

                    <p class="card-description">
                        Produk dengan stok 5 atau kurang.
                    </p>

                </div>


                <div class="stock-list">

                    @forelse($lowStockProducts as $product)

                        <div class="stock-item">

                            <div>

                                <p class="stock-name">
                                    {{ $product->name }}
                                </p>

                                <p class="stock-category">
                                    {{ $product->category ?: 'Tanpa kategori' }}
                                </p>

                            </div>

                            <span class="stock-badge">
                                {{ $product->stock }}
                            </span>

                        </div>

                    @empty

                        <div class="stock-safe">
                            Semua stok produk masih aman.
                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</div>

@endsection