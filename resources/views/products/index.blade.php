@extends('layouts.app')

@section('content')
<x-admin-page title="Produk" subtitle="Kelola katalog, harga, stok, dan kategori produk.">
    <x-slot:actions>
        <a class="workspace-button workspace-button-light" href="{{ route('categories.index') }}">Kelola kategori</a>
        <a class="workspace-button workspace-button-primary" href="{{ route('products.create') }}">+ Tambah produk</a>
    </x-slot:actions>

    @if(session('success'))
        <div class="workspace-alert workspace-alert-success">{{ session('success') }}</div>
    @endif

    <section class="workspace-card">
        <div class="workspace-card-heading workspace-product-list-heading">
            <div><h2>Daftar produk</h2><p>{{ $products->total() }} produk terdaftar di toko.</p></div>
            <form class="workspace-filter" action="{{ route('products.index') }}" method="GET">
                <label class="sr-only" for="productCategoryFilter">Filter kategori</label>
                <select id="productCategoryFilter" name="category" onchange="this.form.submit()">
                    <option value="">Semua kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->name }}" @selected(request('category') === $category->name)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @if(request('category'))
                    <a class="workspace-button workspace-button-light" href="{{ route('products.index') }}">Reset</a>
                @endif
            </form>
        </div>
        @if(session('error'))
            <div class="workspace-alert workspace-alert-error">{{ session('error') }}</div>
        @endif
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead><tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><strong>{{ $product->name }}</strong>@if($product->description)<small>{{ Str::limit($product->description, 60) }}</small>@endif</td>
                            <td><span class="workspace-tag">{{ $product->category ?: 'Tanpa kategori' }}</span></td>
                            <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td>{{ $product->stock }}</td>
                            <td><span class="workspace-status {{ $product->is_active ? 'workspace-status-active' : 'workspace-status-inactive' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <div class="workspace-row-actions">
                                    <a class="workspace-button workspace-button-light" href="{{ route('products.show', $product) }}">Detail</a>
                                    <a class="workspace-button workspace-button-light" href="{{ route('products.edit', $product) }}">Edit</a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Nonaktifkan produk {{ $product->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="workspace-button workspace-button-danger" type="submit" @disabled(!$product->is_active)>Nonaktifkan</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="workspace-empty">Belum ada produk dalam kategori ini. <a href="{{ route('products.create') }}">Tambah produk pertama</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="workspace-pagination">{{ $products->links() }}</div>
    </section>
</x-admin-page>
@endsection
