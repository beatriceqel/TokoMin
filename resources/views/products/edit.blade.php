@extends('layouts.app')

@section('content')
<x-admin-page title="Edit Produk" subtitle="Perbarui informasi produk yang tersimpan di TokoMini.">
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
            <div><h2>{{ $product->name }}</h2><p>Pastikan stok dan kategori tetap sesuai.</p></div>
        </div>
        <form action="{{ route('products.update', $product) }}" method="POST" class="workspace-form workspace-product-form">
            @csrf
            @method('PUT')
            @include('products._form')
            <div class="workspace-form-actions">
                <a class="workspace-button workspace-button-light" href="{{ route('products.index') }}">Batal</a>
                <button class="workspace-button workspace-button-primary" type="submit">Simpan perubahan</button>
            </div>
        </form>
    </section>
</x-admin-page>
@endsection
