@extends('layouts.app')

@section('content')
<section class="pos-page">
    <div class="pos-container">
        <header class="pos-header">
            <div>
                <p class="pos-eyebrow">TOKOMINI · POINT OF SALE</p>
                <h1>Transaksi Baru</h1>
                <p class="pos-subtitle">Pilih produk, atur pembayaran, lalu selesaikan transaksi.</p>
            </div>
            <div class="pos-user-card">
                <span class="pos-user-mark" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <span>
                    <strong>{{ auth()->user()->name }}</strong>
                    <small id="currentDateTime"></small>
                </span>
            </div>
        </header>

        @if(session('success'))
            <div class="pos-alert pos-alert-success" role="status">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="pos-alert pos-alert-error" role="alert">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="pos-alert pos-alert-error" role="alert">
                <strong>Transaksi belum dapat diproses.</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div id="transactionFeedback" class="pos-alert pos-alert-error" role="alert" hidden></div>

        <form id="transactionForm" class="pos-layout" action="{{ route('transactions.store') }}" method="POST">
            @csrf
            <section class="pos-panel pos-catalog" aria-labelledby="catalogTitle">
                <div class="pos-panel-heading">
                    <div>
                        <p class="pos-eyebrow">KATALOG</p>
                        <h2 id="catalogTitle">Pilih Produk</h2>
                    </div>
                    <span class="pos-count">{{ $products->count() }} produk</span>
                </div>

                <div class="pos-catalog-filters">
                    <label class="pos-search">
                        <span class="sr-only">Cari produk</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m16 16 4 4"></path></svg>
                        <input id="searchProduct" type="search" placeholder="Cari nama produk..." autocomplete="off">
                    </label>
                    <label class="pos-category-filter">
                        <span class="sr-only">Filter kategori</span>
                        <select id="categoryFilter">
                            <option value="">Semua kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->name }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div id="productGrid" class="pos-product-grid">
                    @forelse($products as $product)
                        <article
                            class="pos-product-card product-card"
                            data-name="{{ $product->name }}"
                            data-category="{{ $product->category ?? '' }}"
                            data-code="{{ $product->code ?? '' }}"
                            data-barcode="{{ $product->barcode ?? '' }}"
                        >
                            <div class="pos-product-image">
                                @if($product->image)
                                    <img src="{{ asset('images/products/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy">
                                @else
                                    <span aria-hidden="true">📦</span>
                                @endif
                                <span class="pos-stock-badge">Stok {{ $product->stock }}</span>
                            </div>
                            <div class="pos-product-info">
                                <span class="pos-product-category">{{ $product->category ?: 'Produk TokoMini' }}</span>
                                <h3>{{ $product->name }}</h3>
                                <div class="pos-product-bottom">
                                    <strong>Rp {{ number_format($product->price, 0, ',', '.') }}</strong>
                                    <button
                                        class="pos-add-button add-product-btn"
                                        type="button"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        data-price="{{ $product->price }}"
                                        data-stock="{{ $product->stock }}"
                                        aria-label="Tambah {{ $product->name }} ke keranjang"
                                    >Tambah <span aria-hidden="true">+</span></button>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="pos-empty-catalog">
                            <span aria-hidden="true">🛍️</span>
                            <strong>Belum ada produk tersedia</strong>
                            <p>Produk aktif dengan stok tersedia akan muncul di sini.</p>
                        </div>
                    @endforelse
                </div>
                <p id="noProductsFound" class="pos-no-results" hidden>Produk tidak ditemukan. Coba kata kunci lain.</p>
            </section>

            <aside class="pos-sidebar">
                <section class="pos-panel pos-cart" aria-labelledby="cartTitle">
                    <div class="pos-panel-heading pos-cart-heading">
                        <div>
                            <p class="pos-eyebrow">PESANAN</p>
                            <h2 id="cartTitle">Keranjang</h2>
                            <span id="cartCount" class="pos-cart-count">0 item</span>
                        </div>
                        <button id="clearCart" class="pos-text-button" type="button">Kosongkan</button>
                    </div>

                    <div class="pos-cart-items" id="cartItems">
                        <div class="pos-empty-cart" id="emptyCart">
                            <span aria-hidden="true">🧺</span>
                            <strong>Keranjang masih kosong</strong>
                            <p>Tambahkan produk dari katalog untuk memulai.</p>
                        </div>
                        <div id="cartBody" class="pos-cart-list"></div>
                    </div>

                    <div class="pos-total-list">
                        <div class="pos-total-row"><span>Subtotal</span><strong id="subtotalText">Rp 0</strong></div>
                        <label class="pos-number-row" for="discount">
                            <span>Diskon</span>
                            <span class="pos-number-input"><span>Rp</span><input id="discount" name="discount" type="number" min="0" step="1000" value="0"></span>
                        </label>
                        <label class="pos-number-row" for="tax">
                            <span>Pajak</span>
                            <span class="pos-number-input"><span>Rp</span><input id="tax" name="tax" type="number" min="0" step="1000" value="0"></span>
                        </label>
                        <label class="pos-number-row" for="other_fee">
                            <span>Biaya lain</span>
                            <span class="pos-number-input"><span>Rp</span><input id="other_fee" name="other_fee" type="number" min="0" step="1000" value="0"></span>
                        </label>
                        <div class="pos-grand-total"><span>Total pembayaran</span><strong id="grandTotalText">Rp 0</strong></div>
                    </div>
                </section>

                <section class="pos-panel pos-payment" aria-labelledby="paymentTitle">
                    <div class="pos-panel-heading">
                        <div>
                            <p class="pos-eyebrow">SELESAIKAN PESANAN</p>
                            <h2 id="paymentTitle">Pembayaran</h2>
                        </div>
                    </div>

                    <label class="pos-field" for="customer_id">
                        <span>Pelanggan</span>
                        <select id="customer_id" name="customer_id">
                            <option value="">Tanpa pelanggan</option>
                            @foreach(\App\Models\User::orderBy('name')->get() as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="pos-field" for="payment_method">
                        <span>Metode pembayaran</span>
                        <select id="payment_method" name="payment_method" required>
                            <option value="tunai">Tunai</option>
                            <option value="qris">QRIS</option>
                            <option value="debit">Debit</option>
                            <option value="kredit">Kredit</option>
                            <option value="e-wallet">E-Wallet</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </label>

                    <label class="pos-field" for="paid_amount">
                        <span>Uang dibayar</span>
                        <span class="pos-payment-input"><span>Rp</span><input id="paid_amount" name="paid_amount" type="number" min="0" step="1" placeholder="0" required></span>
                    </label>

                    <div class="pos-change">
                        <span>Kembalian</span>
                        <strong id="changeText">Rp 0</strong>
                    </div>

                    <div id="hiddenCartInputs"></div>
                    <div class="pos-form-actions">
                        <button id="resetBtn" class="pos-reset-button" type="button">Reset</button>
                        <button id="submitBtn" class="pos-submit-button" type="submit">
                            <span aria-hidden="true">✓</span> Bayar &amp; cetak
                        </button>
                    </div>
                    <p class="pos-secure-note">Pastikan pesanan dan pembayaran sudah benar.</p>
                </section>
            </aside>
        </form>
    </div>
</section>
@endsection
