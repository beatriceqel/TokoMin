<div class="workspace-form-grid">
    <label class="workspace-field">
        <span>Nama produk</span>
        <input name="name" value="{{ old('name', $product->name ?? '') }}" maxlength="255" required placeholder="Contoh: Kaos polos">
        @error('name')<small class="workspace-field-error">{{ $message }}</small>@enderror
    </label>

    <label class="workspace-field">
        <span>Kategori</span>
        <select name="category" required>
            <option value="">Pilih kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->name }}" @selected(old('category', $product->category ?? '') === $category->name)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category')<small class="workspace-field-error">{{ $message }}</small>@enderror
        <small class="workspace-helper">Kategori belum tersedia? <a href="{{ route('categories.index') }}">Kelola kategori</a>.</small>
    </label>

    <label class="workspace-field workspace-field-full">
        <span>Deskripsi <small>(opsional)</small></span>
        <textarea name="description" rows="4" placeholder="Informasi tambahan tentang produk">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description')<small class="workspace-field-error">{{ $message }}</small>@enderror
    </label>

    <label class="workspace-field">
        <span>Harga (Rp)</span>
        <input name="price" type="number" min="0" step="1" value="{{ old('price', $product->price ?? '') }}" required>
        @error('price')<small class="workspace-field-error">{{ $message }}</small>@enderror
    </label>

    <label class="workspace-field">
        <span>Stok</span>
        <input name="stock" type="number" min="0" step="1" value="{{ old('stock', $product->stock ?? 0) }}" required>
        @error('stock')<small class="workspace-field-error">{{ $message }}</small>@enderror
    </label>

    <label class="workspace-field workspace-field-full">
        <span>Nama file gambar <small>(opsional)</small></span>
        <input name="image" value="{{ old('image', $product->image ?? '') }}" maxlength="255" placeholder="Contoh: produk.jpg">
        @error('image')<small class="workspace-field-error">{{ $message }}</small>@enderror
    </label>
</div>

<label class="workspace-checkbox">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>
    <span><strong>Produk aktif</strong><small>Produk aktif dengan stok tersedia dapat dijual di kasir.</small></span>
</label>
