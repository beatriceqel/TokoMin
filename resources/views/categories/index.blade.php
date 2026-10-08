@extends('layouts.app')

@section('content')
<x-admin-page
    title="Kategori Produk"
    subtitle="Atur kategori supaya produk mudah ditemukan di katalog kasir."
>
    @if(session('success'))
        <div class="workspace-alert workspace-alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="workspace-alert workspace-alert-error">
            <strong>Perubahan kategori belum dapat disimpan.</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="workspace-columns">
        <section class="workspace-card workspace-category-create">
            <div class="workspace-card-heading">
                <div>
                    <h2>Tambah kategori</h2>
                    <p>Buat pilihan kategori baru untuk produk.</p>
                </div>
            </div>
            <form action="{{ route('categories.store') }}" method="POST" class="workspace-form">
                @csrf
                <label for="newCategoryName">Nama kategori</label>
                <input id="newCategoryName" name="name" value="{{ old('name') }}" maxlength="100" required placeholder="Contoh: Pakaian">
                <button class="workspace-button workspace-button-primary" type="submit">Tambah kategori</button>
            </form>
        </section>

        <section class="workspace-card workspace-category-list">
            <div class="workspace-card-heading">
                <div>
                    <h2>Daftar kategori</h2>
                    <p>{{ $categories->count() }} kategori terdaftar</p>
                </div>
            </div>
            <div class="workspace-table-wrap">
                <table class="workspace-table">
                    <thead>
                        <tr><th>Kategori</th><th>Produk</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    <form class="workspace-category-edit" action="{{ route('categories.update', $category) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <label class="sr-only" for="category-{{ $category->id }}">Nama kategori {{ $category->name }}</label>
                                        <input id="category-{{ $category->id }}" name="name" value="{{ $category->name }}" maxlength="100" required>
                                        <button class="workspace-button workspace-button-light" type="submit">Simpan</button>
                                    </form>
                                </td>
                                <td>{{ $category->products_count }}</td>
                                <td>
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="workspace-button workspace-button-danger" type="submit" @disabled($category->products_count > 0)>Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="workspace-empty">Belum ada kategori.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="workspace-helper">Kategori yang sedang digunakan produk tidak bisa dihapus. Ubah nama kategori akan memperbarui kategori produk terkait.</p>
        </section>
    </div>
</x-admin-page>
@endsection
