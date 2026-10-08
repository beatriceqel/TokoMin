<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $transaction->transaction_number }}</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace, Arial, sans-serif;
        }

        body {
            margin: 0;
            padding: 20px;
            background: #f3f4f6;
            color: #111827;
            display: flex;
            justify-content: center;
        }

        /* UKURAN STANDAR STRUK KASIR (58mm ~ 300px) */
        .receipt-wrapper {
            width: 300px;
            margin: 0 auto;
        }
        .receipt {
            background: white;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
            font-size: 12px;
            line-height: 1.4;
        }
        .brand-tag {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .store-name {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .store-info {
            text-align: center;
            color: #4b5563;
            font-size: 11px;
            line-height: 1.3;
        }
        .line {
            border-top: 1px dashed #6b7280;
            margin: 10px 0;
        }
        .transaction-info {
            font-size: 11px;
            line-height: 1.5;
        }
        .transaction-info-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .transaction-info-row span:first-child {
            color: #374151;
        }
        /* LIST BARANG */
        .items {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .items th {
            text-align: left;
            padding-bottom: 5px;
            border-bottom: 1px dashed #6b7280;
        }
        .items td {
            padding: 4px 0;
            vertical-align: top;
        }
        .text-right {
            text-align: right !important;
        }
        .item-name {
            font-weight: bold;
            display: block;
        }
        .item-detail {
            display: block;
            color: #4b5563;
            font-size: 11px;
        }
        /* RINGKASAN PEMBAYARAN */
        .summary {
            font-size: 11px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .summary-row.total {
            font-size: 14px;
            font-weight: bold;
            padding-top: 6px;
            margin-top: 6px;
            border-top: 1px dashed #6b7280;
        }
        .summary-row.change {
            font-weight: bold;
        }
        .payment-method {
            text-transform: uppercase;
        }
        .thank-you {
            text-align: center;
            margin-top: 12px;
            font-size: 11px;
            color: #374151;
            line-height: 1.4;
        }
        .button-area {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
        }
        .button {
            border: none;
            border-radius: 5px;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        .button-print {
            background: #13825d;
            color: white;
        }
        .button-back {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        /* =========================================
           MODE CETAK (KERTAS THERMAL 58MM)
        ========================================= */
        @media print {
            @page {
                size: 58mm auto;
                margin: 0;
            }
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .receipt-wrapper {
                width: 100%;
                max-width: none;
            }
            .receipt {
                box-shadow: none;
                border-radius: 0;
                padding: 8px;
            }
            .button-area {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="receipt-wrapper">
    <div class="receipt">
        {{-- HEADER TOKO --}}
        <div class="brand-tag">DSCHMX.GDL</div>
        <div class="store-name">TokoMini</div>
        <div class="store-info">
            Sabu Raijua - Nusa Tenggara Timur
        </div>
        <div class="line"></div>
        {{-- INFORMASI TRANSAKSI --}}
        <div class="transaction-info">
            <div class="transaction-info-row">
                <span>No. Transaksi</span>
                <strong>{{ $transaction->transaction_number }}</strong>
            </div>
            <div class="transaction-info-row">
                <span>Tanggal</span>
                <span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="transaction-info-row">
                <span>Kasir</span>
                <span>{{ $transaction->user->name ?? '-' }}</span>
            </div>
            <div class="transaction-info-row">
                <span>Pelanggan</span>
                <span>{{ $transaction->customer->name ?? 'Umum' }}</span>
            </div>
        </div>
        <div class="line"></div>
        {{-- DETAIL BARANG --}}
        <table class="items">
            <thead>
                <tr>
                    <th>Barang</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->details as $detail)
                    <tr>
                        <td>
                            <span class="item-name">
                                {{ $detail->product_name ?? $detail->product->name ?? '-' }}
                            </span>
                            <span class="item-detail">
                                {{ $detail->qty }} x Rp {{ number_format($detail->price, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="text-right">
                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="line"></div>
        {{-- RINGKASAN PEMBAYARAN --}}
        <div class="summary">
            <div class="summary-row">
                <span>Subtotal</span>
                <span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($transaction->discount > 0)
            <div class="summary-row">
                <span>Diskon</span>
                <span>Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($transaction->tax > 0)
            <div class="summary-row">
                <span>Pajak</span>
                <span>Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($transaction->other_fee > 0)
            <div class="summary-row">
                <span>Biaya Lain</span>
                <span>Rp {{ number_format($transaction->other_fee, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="summary-row total">
                <span>TOTAL</span>
                <span>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span>Bayar</span>
                <span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row change">
                <span>Kembalian</span>
                <span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span>Metode</span>
                <span class="payment-method">{{ $transaction->payment_method }}</span>
            </div>
        </div>
        <div class="line"></div>
        {{-- FOOTER --}}
        <div class="thank-you">
            <strong>Transaksi berhasil!</strong><br>
            Terima kasih telah berbelanja.<br>
            Semoga hari Anda menyenangkan.
        </div>
    </div>
    {{-- BUTTON AREA --}}
    <div class="button-area">
        <button type="button" class="button button-print" onclick="window.print()">
            🖨 Cetak Struk
        </button>
        <a href="{{ route('transactions.create') }}" class="button button-back">
            ← Transaksi Baru
        </a>
    </div>
</div>
<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 500);
    });
</script>
</body>
</html>