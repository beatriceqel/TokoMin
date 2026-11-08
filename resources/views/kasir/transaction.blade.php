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
                <span class="pos-user-mark" aria-hidden="true">{{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'K' }}</span>
                <span>
                    <strong>{{ auth()->user()->name ?? 'Kasir' }}</strong>
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
                            @foreach($categories ?? [] as $category)
                                @if(is_object($category))
                                    <option value="{{ $category->name }}">{{ $category->name }}</option>
                                @endif
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
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy">
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
                        <span class="pos-payment-input"><span>Rp</span><input id="paid_amount" name="paid_amount" type="number" min="0" step="1" placeholder="0"></span>
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

<!-- MODAL POPUP STRUK / NOTIFIKASI -->
<div id="receiptModal" class="pos-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="pos-modal-content" style="background: white; padding: 24px; border-radius: 8px; width: 420px; max-width: 90%;">
        <h3 style="margin-bottom: 12px; font-weight: bold; font-size: 18px;">Pembayaran Berhasil</h3>
        <div id="receiptDetails" style="margin-bottom: 16px; font-size: 14px; background: #f9f9f9; padding: 12px; border-radius: 4px;"></div>
        <div style="display: flex; gap: 8px; justify-content: flex-end; flex-wrap: wrap;">
            <button type="button" id="closeModalBtn" style="padding: 8px 16px; background: #6b7280; color: white; border: none; border-radius: 4px; cursor: pointer;">Tutup</button>
            <a href="#" id="printReceiptBtn" target="_blank" style="padding: 8px 16px; background: #2563eb; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">Cetak Struk</a>
            <a href="#" id="downloadPdfBtn" target="_blank" style="padding: 8px 16px; background: #059669; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">Unduh Invoice PDF</a>
        </div>
    </div>
</div>

<!-- SCRIPTS MIDTRANS SNAP -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.clientKey') }}"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let cart = [];

    function updateCartUI() {
        const cartBody = document.getElementById('cartBody');
        const emptyCart = document.getElementById('emptyCart');
        const cartCount = document.getElementById('cartCount');
        const subtotalText = document.getElementById('subtotalText');
        const grandTotalText = document.getElementById('grandTotalText');
        const hiddenCartInputs = document.getElementById('hiddenCartInputs');
        const paymentMethod = document.getElementById('payment_method').value;
        const paidInput = document.getElementById('paid_amount');

        cartBody.innerHTML = '';
        hiddenCartInputs.innerHTML = '';

        if (cart.length === 0) {
            emptyCart.style.display = 'block';
            cartCount.textContent = '0 item';
            subtotalText.textContent = 'Rp 0';
            grandTotalText.textContent = 'Rp 0';
            return;
        }

        emptyCart.style.display = 'none';
        let totalItems = 0;
        let subtotal = 0;

        cart.forEach((item, index) => {
            totalItems += item.qty;
            let itemSubtotal = item.price * item.qty;
            subtotal += itemSubtotal;

            cartBody.innerHTML += `
                <div class="pos-cart-item" style="display:flex; justify-content:space-between; margin-bottom:8px; align-items:center;">
                    <div>
                        <strong>${item.name}</strong><br>
                        <small>Rp ${item.price.toLocaleString()} x ${item.qty}</small>
                    </div>
                    <div>
                        <span>Rp ${itemSubtotal.toLocaleString()}</span>
                        <button type="button" class="remove-item" data-index="${index}" style="color:red; margin-left:8px; border:none; background:none; cursor:pointer;">×</button>
                    </div>
                </div>
            `;

            hiddenCartInputs.innerHTML += `
                <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
            `;
        });

        cartCount.textContent = `${totalItems} item`;
        subtotalText.textContent = `Rp ${subtotal.toLocaleString()}`;

        let discount = parseFloat(document.getElementById('discount').value) || 0;
        let tax = parseFloat(document.getElementById('tax').value) || 0;
        let otherFee = parseFloat(document.getElementById('other_fee').value) || 0;

        let grandTotal = subtotal - discount + tax + otherFee;
        if (grandTotal < 0) grandTotal = 0;

        grandTotalText.textContent = `Rp ${grandTotal.toLocaleString()}`;

        if (paymentMethod !== 'tunai') {
            paidInput.value = grandTotal;
            paidInput.readOnly = true;
        } else {
            paidInput.readOnly = false;
        }

        let paidAmount = parseFloat(paidInput.value) || 0;
        let change = paidAmount - grandTotal;
        document.getElementById('changeText').textContent = `Rp ${change >= 0 ? change.toLocaleString() : 0}`;
    }

    document.getElementById('payment_method').addEventListener('change', updateCartUI);

    document.querySelectorAll('.add-product-btn').forEach(button => {
        button.addEventListener('click', function() {
            let id = parseInt(this.dataset.id);
            let name = this.dataset.name;
            let price = parseFloat(this.dataset.price);
            let stock = parseInt(this.dataset.stock);

            let existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                if (existingItem.qty < stock) {
                    existingItem.qty++;
                } else {
                    alert('Stok produk tidak mencukupi!');
                }
            } else {
                if (stock > 0) {
                    cart.push({ id, name, price, qty: 1, stock });
                } else {
                    alert('Stok produk habis!');
                }
            }
            updateCartUI();
        });
    });

    document.getElementById('cartBody').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            let index = e.target.dataset.index;
            cart.splice(index, 1);
            updateCartUI();
        }
    });

    ['discount', 'tax', 'other_fee', 'paid_amount'].forEach(id => {
        document.getElementById(id).addEventListener('input', updateCartUI);
    });

    document.getElementById('clearCart').addEventListener('click', function() {
        cart = [];
        updateCartUI();
    });

    document.getElementById('resetBtn').addEventListener('click', function() {
        cart = [];
        document.getElementById('transactionForm').reset();
        updateCartUI();
    });

    const form = document.getElementById('transactionForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (cart.length === 0) {
            alert('Keranjang masih kosong!');
            return;
        }

        let paymentMethod = document.getElementById('payment_method').value;
        let formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            const trx = data.transaction;

            if (paymentMethod === 'tunai') {
                showReceiptModal(trx);
            } else {
                fetch(`/transactions/${trx.id}/pay-midtrans`, {
                    headers: {
                        'Accept': 'application/json',
                    }
                })
                .then(res => res.json())
                .then(midtransData => {
                    if (midtransData.success) {
                        window.snap.pay(midtransData.snapToken, {
                            onSuccess: function(result){
                                fetch(`/transactions/${trx.id}/mark-as-paid`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    }
                                })
                                .then(res => res.json())
                                .then(paidData => {
                                    if (paidData.success) {
                                        alert("Pembayaran Midtrans Berhasil & Terkonfirmasi!");
                                        showReceiptModal(trx);
                                    } else {
                                        alert("Gagal memperbarui status transaksi.");
                                    }
                                });
                            },
                            onPending: function(result){
                                alert("Menunggu pembayaran Anda diselesaikan.");
                                location.reload();
                            },
                            onError: function(result){
                                alert("Pembayaran gagal!");
                                console.log(result);
                            },
                            onClose: function(){
                                console.log("Popup ditutup tanpa menyelesaikan pembayaran.");
                            }
                        });
                    } else {
                        alert("Gagal mendapatkan Snap Token: " + midtransData.message);
                    }
                })
                .catch(err => {
                    console.error('Midtrans Error:', err);
                    alert('Terjadi kesalahan saat menghubungkan ke Midtrans.');
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let msg = error.message || 'Terjadi kesalahan saat memproses transaksi.';
            if (error.errors) {
                msg = Object.values(error.errors).flat().join('\n');
            }
            alert(msg);
        });
    });

    function showReceiptModal(trx) {
        const modal = document.getElementById('receiptModal');
        const details = document.getElementById('receiptDetails');
        const printBtn = document.getElementById('printReceiptBtn');
        const pdfBtn = document.getElementById('downloadPdfBtn');

        details.innerHTML = `
            <strong>No. Transaksi:</strong> ${trx.transaction_number}<br>
            <strong>Total:</strong> Rp ${Number(trx.grand_total).toLocaleString()}<br>
            <strong>Metode:</strong> ${trx.payment_method.toUpperCase()}
        `;
        printBtn.href = `/transactions/${trx.id}/receipt`;
        pdfBtn.href = `/orders/${trx.id}/invoice`;
        modal.style.display = 'flex';

        cart = [];
        updateCartUI();
        document.getElementById('transactionForm').reset();
    }

    document.getElementById('closeModalBtn').addEventListener('click', function() {
        document.getElementById('receiptModal').style.display = 'none';
        location.reload();
    });
});
</script>
@endsection