const transactionForm = document.querySelector('#transactionForm');

if (transactionForm) {
    const productCards = [...document.querySelectorAll('.product-card')];
    const products = new Map(productCards.map((card) => {
        const button = card.querySelector('.add-product-btn');
        return [button.dataset.id, {
            id: button.dataset.id,
            name: button.dataset.name,
            price: Number(button.dataset.price),
            stock: Number(button.dataset.stock),
        }];
    }));
    const cart = new Map();
    const cartBody = document.querySelector('#cartBody');
    const hiddenCartInputs = document.querySelector('#hiddenCartInputs');
    const emptyCart = document.querySelector('#emptyCart');
    const feedback = document.querySelector('#transactionFeedback');
    const currency = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    });

    const formatCurrency = (amount) => currency.format(amount);
    const readAmount = (selector) => {
        const amount = Number(document.querySelector(selector).value);
        return Number.isFinite(amount) ? Math.max(0, amount) : 0;
    };

    const showFeedback = (message = '') => {
        feedback.textContent = message;
        feedback.hidden = message.length === 0;
    };

    const createButton = (label, className, action, productId, ariaLabel) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.dataset.action = action;
        button.dataset.productId = productId;
        button.setAttribute('aria-label', ariaLabel);
        button.textContent = label;
        return button;
    };

    const renderCart = () => {
        cartBody.replaceChildren();
        hiddenCartInputs.replaceChildren();
        let itemCount = 0;

        [...cart.values()].forEach((item, index) => {
            itemCount += item.quantity;

            const row = document.createElement('article');
            row.className = 'pos-cart-item';

            const name = document.createElement('span');
            name.className = 'pos-cart-item-name';
            name.textContent = item.name;

            const subtotal = document.createElement('strong');
            subtotal.className = 'pos-cart-item-subtotal';
            subtotal.textContent = formatCurrency(item.price * item.quantity);

            const controls = document.createElement('div');
            controls.className = 'pos-cart-controls';
            controls.append(
                createButton('−', 'pos-qty-button', 'decrease', item.id, `Kurangi ${item.name}`),
            );

            const quantity = document.createElement('span');
            quantity.className = 'pos-qty-value';
            quantity.textContent = item.quantity;
            controls.append(quantity);
            controls.append(
                createButton('+', 'pos-qty-button', 'increase', item.id, `Tambah ${item.name}`),
                createButton('×', 'pos-remove-button', 'remove', item.id, `Hapus ${item.name} dari keranjang`),
            );

            const inputs = document.createDocumentFragment();
            [
                [`items[${index}][product_id]`, item.id],
                [`items[${index}][qty]`, item.quantity],
            ].forEach(([nameValue, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = nameValue;
                input.value = value;
                inputs.append(input);
            });
            hiddenCartInputs.append(inputs);
            row.append(name, subtotal, controls);
            cartBody.append(row);
        });

        emptyCart.hidden = itemCount > 0;
        document.querySelector('#cartCount').textContent = `${itemCount} item`;
        renderTotals();
    };

    const renderTotals = () => {
        const subtotal = [...cart.values()]
            .reduce((total, item) => total + item.price * item.quantity, 0);
        const total = Math.max(
            0,
            subtotal - readAmount('#discount') + readAmount('#tax') + readAmount('#other_fee'),
        );
        const paid = readAmount('#paid_amount');

        document.querySelector('#subtotalText').textContent = formatCurrency(subtotal);
        document.querySelector('#grandTotalText').textContent = formatCurrency(total);
        document.querySelector('#changeText').textContent = formatCurrency(Math.max(0, paid - total));
    };

    document.querySelector('#productGrid').addEventListener('click', (event) => {
        const button = event.target.closest('.add-product-btn');
        if (!button) return;

        const product = products.get(button.dataset.id);
        if (!product) {
            showFeedback('Produk tidak ditemukan. Muat ulang halaman dan coba lagi.');
            return;
        }

        const item = cart.get(product.id);
        if (item && item.quantity >= product.stock) {
            showFeedback(`Stok ${product.name} hanya ${product.stock} item.`);
            return;
        }

        cart.set(product.id, {
            ...product,
            quantity: (item?.quantity ?? 0) + 1,
        });
        showFeedback();
        renderCart();
    });

    cartBody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) return;

        const item = cart.get(button.dataset.productId);
        if (!item) return;

        if (button.dataset.action === 'remove' || (button.dataset.action === 'decrease' && item.quantity === 1)) {
            cart.delete(item.id);
        } else if (button.dataset.action === 'decrease') {
            item.quantity -= 1;
        } else if (button.dataset.action === 'increase') {
            if (item.quantity >= item.stock) {
                showFeedback(`Stok ${item.name} hanya ${item.stock} item.`);
                return;
            }
            item.quantity += 1;
        }

        showFeedback();
        renderCart();
    });

    const filterProducts = () => {
        const query = document.querySelector('#searchProduct').value.trim().toLocaleLowerCase('id-ID');
        const category = document.querySelector('#categoryFilter').value.toLocaleLowerCase('id-ID');
        let visibleCount = 0;

        productCards.forEach((card) => {
            const searchableText = [
                card.dataset.name,
                card.dataset.category,
                card.dataset.code,
                card.dataset.barcode,
            ].join(' ').toLocaleLowerCase('id-ID');
            const matchesSearch = searchableText.includes(query);
            const matchesCategory = !category
                || card.dataset.category.trim().toLocaleLowerCase('id-ID') === category;
            const isVisible = matchesSearch && matchesCategory;
            card.hidden = !isVisible;
            visibleCount += Number(isVisible);
        });

        document.querySelector('#noProductsFound').hidden = visibleCount > 0;
    };

    document.querySelector('#searchProduct').addEventListener('input', filterProducts);
    document.querySelector('#categoryFilter').addEventListener('change', filterProducts);

    ['#discount', '#tax', '#other_fee', '#paid_amount'].forEach((selector) => {
        document.querySelector(selector).addEventListener('input', renderTotals);
    });

    document.querySelector('#clearCart').addEventListener('click', () => {
        cart.clear();
        showFeedback();
        renderCart();
    });

    document.querySelector('#resetBtn').addEventListener('click', () => {
        transactionForm.reset();
        cart.clear();
        filterProducts();
        showFeedback();
        renderCart();
    });

    transactionForm.addEventListener('submit', (event) => {
        if (cart.size === 0) {
            event.preventDefault();
            showFeedback('Keranjang masih kosong. Tambahkan produk sebelum membayar.');
            return;
        }

        const subtotal = [...cart.values()]
            .reduce((total, item) => total + item.price * item.quantity, 0);
        const total = Math.max(
            0,
            subtotal - readAmount('#discount') + readAmount('#tax') + readAmount('#other_fee'),
        );

        if (readAmount('#paid_amount') < total) {
            event.preventDefault();
            showFeedback('Uang dibayar belum mencukupi total pembayaran.');
            document.querySelector('#paid_amount').focus();
            return;
        }

        showFeedback();
        const submitButton = document.querySelector('#submitBtn');
        submitButton.disabled = true;
        submitButton.textContent = 'Memproses transaksi...';
    });

    const updateDateTime = () => {
        document.querySelector('#currentDateTime').textContent = new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(new Date());
    };

    updateDateTime();
    window.setInterval(updateDateTime, 60_000);
    renderCart();
}
