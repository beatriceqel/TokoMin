@extends('layouts.app')

@section('content')
<x-admin-page
    :title="'Transaksi ' . $transaction->transaction_number"
    subtitle="Rincian produk dan pembayaran transaksi."
    eyebrow="TOKOMINI · RIWAYAT PENJUALAN"
>
    <x-slot:actions>
        <a class="workspace-button workspace-button-light" href="{{ route('transactions.index') }}">Kembali</a>
        <a class="workspace-button workspace-button-primary" href="{{ route('transactions.receipt', $transaction) }}" target="_blank" rel="noopener">Cetak struk</a>
    </x-slot:actions>

    <section class="workspace-card workspace-transaction-summary">
        <div><span>Kasir</span><strong>{{ $transaction->user->name ?? '-' }}</strong></div>
        <div><span>Tanggal</span><strong>{{ $transaction->created_at?->format('d M Y, H:i') ?? '-' }}</strong></div>
        <div><span>Pembayaran</span><strong>{{ strtoupper($transaction->payment_method ?? '-') }}</strong></div>
        <div><span>Status</span><strong><span class="workspace-status {{ $transaction->status === 'completed' ? 'workspace-status-active' : 'workspace-status-inactive' }}">{{ ucfirst($transaction->status ?? '-') }}</span></strong></div>
    </section>

    <section class="workspace-card workspace-transaction-items">
        <div class="workspace-card-heading"><div><h2>Produk dalam transaksi</h2><p>{{ $transaction->details->count() }} baris produk</p></div></div>
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead><tr><th>Produk</th><th>Harga satuan</th><th>Jumlah</th><th>Diskon</th><th>Subtotal</th></tr></thead>
                <tbody>
                    @forelse($transaction->details as $detail)
                        <tr>
                            <td><strong>{{ $detail->product_name ?? $detail->product->name ?? 'Produk tidak ditemukan' }}</strong>@if($detail->product_code)<small>Kode: {{ $detail->product_code }}</small>@endif</td>
                            <td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td>{{ $detail->qty }}</td>
                            <td>Rp {{ number_format($detail->discount ?? 0, 0, ',', '.') }}</td>
                            <td><strong>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="workspace-empty">Tidak ada detail produk pada transaksi ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="workspace-columns workspace-transaction-bottom">
        <section class="workspace-card">
            <div class="workspace-card-heading"><div><h2>Pembayaran</h2><p>Rincian pembayaran pelanggan</p></div></div>
            <div class="workspace-summary-lines">
                <div><span>Metode</span><strong>{{ strtoupper($transaction->payment_method ?? '-') }}</strong></div>
                <div><span>Uang dibayar</span><strong>Rp {{ number_format($transaction->paid_amount ?? 0, 0, ',', '.') }}</strong></div>
                <div><span>Kembalian</span><strong>Rp {{ number_format($transaction->change_amount ?? 0, 0, ',', '.') }}</strong></div>
            </div>
        </section>
        <section class="workspace-card">
            <div class="workspace-card-heading"><div><h2>Ringkasan total</h2><p>Perhitungan transaksi</p></div></div>
            <div class="workspace-summary-lines">
                <div><span>Subtotal</span><strong>Rp {{ number_format($transaction->subtotal ?? 0, 0, ',', '.') }}</strong></div>
                <div><span>Diskon</span><strong>Rp {{ number_format($transaction->discount ?? 0, 0, ',', '.') }}</strong></div>
                <div><span>Pajak</span><strong>Rp {{ number_format($transaction->tax ?? 0, 0, ',', '.') }}</strong></div>
                <div><span>Biaya lain</span><strong>Rp {{ number_format($transaction->other_fee ?? 0, 0, ',', '.') }}</strong></div>
                <div class="workspace-summary-grand"><span>Total pembayaran</span><strong>Rp {{ number_format($transaction->grand_total ?? 0, 0, ',', '.') }}</strong></div>
            </div>
        </section>
    </div>
</x-admin-page>
@endsection
