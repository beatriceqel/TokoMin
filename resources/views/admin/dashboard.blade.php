@extends('layouts.app')

@section('content')
<x-admin-page title="Selamat datang, {{ auth()->user()->name }}" subtitle="Kelola produk, kategori, dan transaksi TokoMini dari satu tempat." eyebrow="TOKOMINI · DASHBOARD ADMIN">
    <section class="workspace-welcome">
        <div>
            <span class="workspace-welcome-icon" aria-hidden="true">✦</span>
            <p class="workspace-eyebrow">PANEL ADMINISTRATOR</p>
            <h2>Operasional toko lebih mudah.</h2>
            <p>Atur katalog toko atau tinjau aktivitas transaksi terbaru.</p>
        </div>
        <a class="workspace-button workspace-button-primary" href="{{ route('products.create') }}">+ Tambah produk</a>
    </section>

    <div class="workspace-dashboard-grid">
        <a class="workspace-dashboard-card" href="{{ route('products.index') }}">
            <span class="workspace-dashboard-icon workspace-icon-green" aria-hidden="true">▦</span>
            <span><strong>Produk</strong><small>Kelola katalog, harga, stok, dan status produk.</small></span>
            <span class="workspace-dashboard-arrow" aria-hidden="true">→</span>
        </a>
        <a class="workspace-dashboard-card" href="{{ route('categories.index') }}">
            <span class="workspace-dashboard-icon workspace-icon-amber" aria-hidden="true">◈</span>
            <span><strong>Kategori</strong><small>Tambah dan rapikan pilihan kategori produk.</small></span>
            <span class="workspace-dashboard-arrow" aria-hidden="true">→</span>
        </a>
        <a class="workspace-dashboard-card" href="{{ route('transactions.index') }}">
            <span class="workspace-dashboard-icon workspace-icon-blue" aria-hidden="true">▤</span>
            <span><strong>Riwayat transaksi</strong><small>Lihat transaksi yang sudah dilakukan.</small></span>
            <span class="workspace-dashboard-arrow" aria-hidden="true">→</span>
        </a>
    </div>

    <div class="workspace-note"><strong>Tips:</strong> Kategori yang dibuat di menu kategori langsung tersedia pada formulir produk dan filter katalog kasir.</div>
</x-admin-page>
@endsection
