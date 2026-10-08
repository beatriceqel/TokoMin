@extends('layouts.app')

@section('content')
<x-admin-page title="Tambah Produk" subtitle="Lengkapi informasi produk untuk menambahkannya ke katalog toko.">
    @if($errors->any())
        <div class="workspace-alert workspace-alert-error">
            <strong>Periksa kembali informasi produk.</strong>
            <ul>
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <section class="workspace-card workspace-form-card">
        <div class="workspace-card-heading">
            <div><h2>Informasi produk</h2><p>Nama, kategori, harga, dan stok akan tampil di katalog kasir.</p></div>
        </div>
        <form action="{{ route('products.store') }}" method="POST" class="workspace-form workspace-product-form">
            @csrf
            @include('products._form')
            <div class="workspace-form-actions">
                <a class="workspace-button workspace-button-light" href="{{ route('products.index') }}">Batal</a>
                <button class="workspace-button workspace-button-primary" type="submit">Simpan produk</button>
            </div>
        </form>
    </section>
</x-admin-page>
@endsection
