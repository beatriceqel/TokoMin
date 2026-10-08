@extends('layouts.app')

@section('content')

<style>
    .history-page {
        min-height: calc(100vh - 70px);
        background: #f5f7f8;
        padding: 35px 25px 50px;
    }

    .history-container {
        max-width: 1250px;
        margin: 0 auto;
    }

    .history-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 25px;
    }

    .history-title {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #111827;
    }

    .history-subtitle {
        margin: 7px 0 0;
        font-size: 14px;
        color: #6b7280;
    }

    .btn-new-transaction {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 18px;
        background: #13825d;
        color: white;
        text-decoration: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        transition: 0.2s;
        white-space: nowrap;
    }

    .btn-new-transaction:hover {
        background: #0d704f;
        transform: translateY(-1px);
    }

    .alert-success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
        padding: 14px 18px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 14px;
        font-weight: 600;
    }

    .history-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .history-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 22px 25px;
        border-bottom: 1px solid #e5e7eb;
    }

    .history-card-title {
        margin: 0;
        font-size: 19px;
        font-weight: 700;
        color: #111827;
    }

    .history-card-description {
        margin: 5px 0 0;
        font-size: 13px;
        color: #6b7280;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }

    .history-table thead {
        background: #f8fafc;
    }

    .history-table th {
        padding: 14px 18px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    .history-table td {
        padding: 17px 18px;
        font-size: 14px;
        color: #374151;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .history-table tbody tr {
        transition: background 0.15s;
    }

    .history-table tbody tr:hover {
        background: #f8fafc;
    }

    .invoice-number {
        font-weight: 700;
        color: #16815b;
    }

    .cashier-name {
        font-weight: 600;
        color: #374151;
    }

    .total-price {
        font-weight: 700;
        color: #111827;
        white-space: nowrap;
    }

    .payment-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        border-radius: 999px;
        background: #e9f7ef;
        color: #16815b;
        font-size: 12px;
        font-weight: 700;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        border-radius: 999px;
        background: #ecfdf5;
        color: #047857;
        font-size: 12px;
        font-weight: 700;
    }

    .status-pending {
        background: #fffbeb;
        color: #b45309;
    }

    .status-cancelled {
        background: #fef2f2;
        color: #b91c1c;
    }

    .transaction-date {
        color: #6b7280;
        white-space: nowrap;
        line-height: 1.5;
    }

    .transaction-date strong {
        display: block;
        color: #374151;
        font-weight: 600;
    }

    .action-buttons {
        display: flex;
        align-items: center;
        gap: 7px;
        white-space: nowrap;
    }

    .btn-detail,
    .btn-receipt {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
        transition: 0.2s;
    }

    .btn-detail {
        background: #e9f7ef;
        color: #16815b;
        border: 1px solid #dbeafe;
    }

    .btn-detail:hover {
        background: #dbeafe;
    }

    .btn-receipt {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
    }

    .btn-receipt:hover {
        background: #e2e8f0;
    }

    .empty-state {
        padding: 70px 25px;
        text-align: center;
    }

    .empty-icon {
        width: 65px;
        height: 65px;
        margin: 0 auto 18px;
        border-radius: 50%;
        background: #e9f7ef;
        color: #16815b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 700;
    }

    .empty-title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #374151;
    }

    .empty-description {
        margin: 7px 0 20px;
        color: #6b7280;
        font-size: 14px;
    }

    .pagination-wrapper {
        padding: 20px 25px;
        border-top: 1px solid #e5e7eb;
    }

    @media (max-width: 700px) {
        .history-page {
            padding: 25px 15px 40px;
        }

        .history-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .btn-new-transaction {
            width: 100%;
            justify-content: center;
        }

        .history-title {
            font-size: 24px;
        }

        .history-card-header {
            padding: 18px;
        }

        .pagination-wrapper {
            padding: 18px;
        }
    }
</style>


<div class="history-page">

    <div class="history-container">

        {{-- HEADER --}}
        <div class="history-header">

            <div>
                <h1 class="history-title">
                    Riwayat Transaksi
                </h1>

                <p class="history-subtitle">
                    Daftar transaksi penjualan TokoMini yang telah dilakukan.
                </p>
            </div>

            <a
                href="{{ route('transactions.create') }}"
                class="btn-new-transaction"
            >
                <span>+</span>
                <span>Transaksi Baru</span>
            </a>

        </div>


        {{-- PESAN BERHASIL --}}
        @if(session('success'))

            <div class="alert-success">
                {{ session('success') }}
            </div>

        @endif


        {{-- CARD RIWAYAT --}}
        <div class="history-card">

            {{-- CARD HEADER --}}
            <div class="history-card-header">

                <div>
                    <h2 class="history-card-title">
                        Daftar Transaksi
                    </h2>

                    <p class="history-card-description">
                        Riwayat transaksi terbaru akan ditampilkan di bawah.
                    </p>
                </div>

            </div>


            {{-- TABLE --}}
            <div class="table-wrapper">

                <table class="history-table">

                    <thead>

                        <tr>

                            <th>
                                No. Transaksi
                            </th>

                            <th>
                                Kasir
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Pembayaran
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Tanggal
                            </th>

                            <th>
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($transactions as $transaction)

                            @php
                                $statusClass = '';

                                if ($transaction->status === 'pending') {
                                    $statusClass = 'status-pending';
                                }

                                if (
                                    $transaction->status === 'cancelled' ||
                                    $transaction->status === 'canceled'
                                ) {
                                    $statusClass = 'status-cancelled';
                                }

                                $paymentLabels = [
                                    'tunai' => 'Tunai',
                                    'qris' => 'QRIS',
                                    'debit' => 'Debit',
                                    'kredit' => 'Kredit',
                                    'e-wallet' => 'E-Wallet',
                                    'transfer' => 'Transfer',
                                ];

                                $paymentLabel =
                                    $paymentLabels[$transaction->payment_method]
                                    ?? ucfirst($transaction->payment_method);
                            @endphp

                            <tr>

                                {{-- NOMOR TRANSAKSI --}}
                                <td>
                                    <div class="invoice-number">
                                        {{ $transaction->transaction_number }}
                                    </div>
                                </td>


                                {{-- KASIR --}}
                                <td>

                                    <div class="cashier-name">
                                        {{ $transaction->user->name ?? 'Kasir' }}
                                    </div>

                                </td>


                                {{-- TOTAL --}}
                                <td>

                                    <div class="total-price">
                                        Rp {{ number_format((float) $transaction->grand_total, 0, ',', '.') }}
                                    </div>

                                </td>


                                {{-- PEMBAYARAN --}}
                                <td>

                                    <span class="payment-badge">
                                        {{ $paymentLabel }}
                                    </span>

                                </td>


                                {{-- STATUS --}}
                                <td>

                                    <span class="status-badge {{ $statusClass }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>

                                </td>


                                {{-- TANGGAL --}}
                                <td>

                                    <div class="transaction-date">

                                        <strong>
                                            {{ $transaction->created_at->format('d/m/Y') }}
                                        </strong>

                                        {{ $transaction->created_at->format('H:i') }}

                                    </div>

                                </td>


                                {{-- AKSI --}}
                                <td>

                                    <div class="action-buttons">

                                        <a
                                            href="{{ route('transactions.show', $transaction) }}"
                                            class="btn-detail"
                                        >
                                            Detail
                                        </a>

                                        <a
                                            href="{{ route('transactions.receipt', $transaction) }}"
                                            class="btn-receipt"
                                        >
                                            Struk
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7">

                                    <div class="empty-state">

                                        <div class="empty-icon">
                                            TRX
                                        </div>

                                        <h3 class="empty-title">
                                            Belum ada transaksi
                                        </h3>

                                        <p class="empty-description">
                                            Transaksi yang sudah selesai akan muncul di sini.
                                        </p>

                                        <a
                                            href="{{ route('transactions.create') }}"
                                            class="btn-new-transaction"
                                            style="display:inline-flex;"
                                        >
                                            + Buat Transaksi
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION --}}
            @if($transactions->hasPages())

                <div class="pagination-wrapper">

                    {{ $transactions->links() }}

                </div>

            @endif

        </div>

    </div>

</div>

@endsection