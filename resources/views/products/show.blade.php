@extends('layouts.app')

@section('content')
<x-admin-page :title="$product->name" subtitle="Informasi lengkap produk yang tersimpan di TokoMini.">
    <x-slot:actions>
        <a class="workspace-button workspace-button-light" href="{{ route('products.index') }}">Kembali ke produk</a>
        <a class="workspace-button workspace-button-primary" href="{{ route('products.edit', $product) }}">Edit produk</a>
    </x-slot:actions>

    <section class="workspace-card workspace-product-detail">
        @if($product->image)
            <div class="workspace-detail-image"><img src="{{ asset('images/products/' . $product->image) }}" alt="{{ $product->name }}"></div>
        @endif
        <div class="workspace-detail-grid">
            <div><span>Kategori</span><strong>{{ $product->category ?: 'Tanpa kategori' }}</strong></div>
            <div><span>Status</span><strong><span class="workspace-status {{ $product->is_active ? 'workspace-status-active' : 'workspace-status-inactive' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span></strong></div>
            <div><span>Harga</span><strong>Rp {{ number_format($product->price, 0, ',', '.') }}</strong></div>
            <div><span>Stok</span><strong>{{ $product->stock }} item</strong></div>
            <div class="workspace-detail-full"><span>Deskripsi</span><p>{{ $product->description ?: 'Belum ada deskripsi.' }}</p></div>
        </div>
    </section>
</x-admin-page>
@endsection
