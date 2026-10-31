<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Events\OrderPlaced; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    /**
     * Menampilkan riwayat transaksi.
     */
    public function index()
    {
        $transactions = Transaction::with('user')
            ->latest()
            ->paginate(10);

        return view(
            'transactions.index',
            compact('transactions')
        );
    }

    /**
     * Menampilkan halaman transaksi kasir.
     */
    public function create()
    {
        $products = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();
        $categories = Category::orderBy('name')->get();

        return view(
            'kasir.transaction',
            compact('products', 'categories')
        );
    }

    /**
     * Menyimpan transaksi baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'items.*.qty' => [
                'required',
                'integer',
                'min:1',
            ],
            'customer_id' => [
                'nullable',
                'integer',
            ],
            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'tax' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'other_fee' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'paid_amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'payment_method' => [
                'required',
                'in:tunai,qris,debit,kredit,e-wallet,transfer',
            ],
        ]);

        $discount = (float) ($validated['discount'] ?? 0);
        $tax = (float) ($validated['tax'] ?? 0);
        $otherFee = (float) ($validated['other_fee'] ?? 0);
        $paidAmount = (float) $validated['paid_amount'];

        $transaction = DB::transaction(function () use (
            $validated,
            $discount,
            $tax,
            $otherFee,
            $paidAmount
        ) {
            $productIds = collect($validated['items'])
                ->pluck('product_id')
                ->unique()
                ->values();

            $products = Product::whereIn(
                'id',
                $productIds
            )
            ->where('is_active', true)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

            foreach ($validated['items'] as $item) {
                $productId = $item['product_id'];
                $qty = (int) $item['qty'];

                if (!isset($products[$productId])) {
                    abort(422, 'Produk tidak ditemukan atau sudah tidak aktif.');
                }

                $product = $products[$productId];

                if ($product->stock < $qty) {
                    abort(422, 'Stock produk "' . $product->name . '" tidak mencukupi.');
                }
            }

            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $productId = $item['product_id'];
                $qty = (int) $item['qty'];
                $product = $products[$productId];

                $itemSubtotal = (float) $product->price * $qty;
                $subtotal += $itemSubtotal;
            }

            $grandTotal = $subtotal - $discount + $tax + $otherFee;

            if ($grandTotal < 0) {
                $grandTotal = 0;
            }

            if ($paidAmount < $grandTotal) {
                abort(422, 'Uang pembayaran tidak mencukupi.');
            }

            $changeAmount = $paidAmount - $grandTotal;

            $transactionNumber = 'TRX-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));

            $trx = Transaction::create([
                'transaction_number' => $transactionNumber,
                'user_id' => auth()->id(),
                'customer_id' => $validated['customer_id'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'other_fee' => $otherFee,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
            ]);

            foreach ($validated['items'] as $item) {
                $productId = $item['product_id'];
                $qty = (int) $item['qty'];
                $product = $products[$productId];

                $itemSubtotal = (float) $product->price * $qty;

                $trx->details()->create([
                    'product_id' => $product->id,
                    'product_code' => null,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'qty' => $qty,
                    'discount' => 0,
                    'subtotal' => $itemSubtotal,
                ]);

                $product->decrement('stock', $qty);
            }

            // TRIGGER BROADCAST EVENT (LARAVEL REVERB)
            event(new OrderPlaced($trx, auth()->user()));

            return $trx;
        });

        // Cek apakah request berasal dari AJAX/Fetch
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan.',
                'transaction' => $transaction
            ]);
        }

        return redirect()
            ->route('transactions.receipt', $transaction)
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    /**
     * Menampilkan detail transaksi.
     */
    public function show(Transaction $transaction)
    {
        $transaction->load([
            'user',
            'details.product',
        ]);

        return view(
            'transactions.show',
            compact('transaction')
        );
    }

    /**
     * Menampilkan struk transaksi.
     */
    public function receipt(Transaction $transaction)
    {
        $transaction->load([
            'user',
            'details.product',
        ]);

        return view(
            'kasir.receipt',
            compact('transaction')
        );
    }

    /**
     * Mencetak dan mengunduh invoice PDF.
     */
    public function invoice($id)
{
    $transaction = Transaction::with('details.product', 'user')->findOrFail($id);

    $invoice = new \stdClass();
    $invoice->id = $transaction->id;
    $invoice->transaction_number = $transaction->transaction_number ?? '-';
    $invoice->created_at = $transaction->created_at ?? now(); 
    $invoice->user = $transaction->user;
    $invoice->payment_method = $transaction->payment_method;
    $invoice->subtotal = $transaction->subtotal;
    $invoice->discount = $transaction->discount;
    $invoice->tax = $transaction->tax;
    $invoice->other_fee = $transaction->other_fee;
    $invoice->paid_amount = $transaction->paid_amount;
    $invoice->change_amount = $transaction->change_amount;

    $invoice->items = $transaction->details->map(function ($item) {
        $item->quantity = $item->qty; 
        $item->total = $item->subtotal ?? ($item->price * $item->qty);
        return $item;
    });

    $invoice->total = $transaction->grand_total ?? 0;

    $pdf = Pdf::loadView('invoices.invoice', compact('invoice'));
    return $pdf->download('invoice_' . $invoice->id . '.pdf');
    }
}