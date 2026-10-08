<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        /*
        |--------------------------------------------------------------------------
        | VALIDASI DATA
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | DEFAULT VALUE
        |--------------------------------------------------------------------------
        */

        $discount = (float) ($validated['discount'] ?? 0);

        $tax = (float) ($validated['tax'] ?? 0);

        $otherFee = (float) ($validated['other_fee'] ?? 0);

        $paidAmount = (float) $validated['paid_amount'];


        /*
        |--------------------------------------------------------------------------
        | PROSES TRANSAKSI DATABASE
        |--------------------------------------------------------------------------
        */

        $transaction = DB::transaction(function () use (
            $validated,
            $discount,
            $tax,
            $otherFee,
            $paidAmount
        ) {

            /*
            |--------------------------------------------------------------------------
            | SIAPKAN PRODUK
            |--------------------------------------------------------------------------
            */

            $productIds = collect($validated['items'])
                ->pluck('product_id')
                ->unique()
                ->values();


            /*
            |--------------------------------------------------------------------------
            | AMBIL PRODUK DAN LOCK ROW
            |--------------------------------------------------------------------------
            */

            $products = Product::whereIn(
                'id',
                $productIds
            )
            ->where('is_active', true)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');


            /*
            |--------------------------------------------------------------------------
            | CEK PRODUK
            |--------------------------------------------------------------------------
            */

            foreach ($validated['items'] as $item) {

                $productId = $item['product_id'];

                $qty = (int) $item['qty'];

                if (!isset($products[$productId])) {

                    abort(
                        422,
                        'Produk tidak ditemukan atau sudah tidak aktif.'
                    );
                }


                $product = $products[$productId];


                /*
                |--------------------------------------------------------------------------
                | CEK STOCK
                |--------------------------------------------------------------------------
                */

                if ($product->stock < $qty) {

                    abort(
                        422,
                        'Stock produk "' .
                        $product->name .
                        '" tidak mencukupi.'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | HITUNG SUBTOTAL
            |--------------------------------------------------------------------------
            */

            $subtotal = 0;


            foreach ($validated['items'] as $item) {

                $productId = $item['product_id'];

                $qty = (int) $item['qty'];

                $product = $products[$productId];


                $itemSubtotal =
                    (float) $product->price * $qty;


                $subtotal += $itemSubtotal;
            }


            /*
            |--------------------------------------------------------------------------
            | HITUNG GRAND TOTAL
            |--------------------------------------------------------------------------
            */

            $grandTotal =
                $subtotal
                - $discount
                + $tax
                + $otherFee;


            /*
            |--------------------------------------------------------------------------
            | CEK TOTAL
            |--------------------------------------------------------------------------
            */

            if ($grandTotal < 0) {

                $grandTotal = 0;
            }


            /*
            |--------------------------------------------------------------------------
            | CEK UANG BAYAR
            |--------------------------------------------------------------------------
            */

            if ($paidAmount < $grandTotal) {

                abort(
                    422,
                    'Uang pembayaran tidak mencukupi.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | HITUNG KEMBALIAN
            |--------------------------------------------------------------------------
            */

            $changeAmount =
                $paidAmount - $grandTotal;


            /*
            |--------------------------------------------------------------------------
            | NOMOR TRANSAKSI
            |--------------------------------------------------------------------------
            */

            $transactionNumber =
                'TRX-' .
                now()->format('YmdHis') .
                '-' .
                strtoupper(
                    Str::random(4)
                );


            /*
            |--------------------------------------------------------------------------
            | SIMPAN TRANSAKSI
            |--------------------------------------------------------------------------
            */

            $transaction = Transaction::create([

                'transaction_number' =>
                    $transactionNumber,

                'user_id' =>
                    auth()->id(),

                'customer_id' =>
                    $validated['customer_id'] ?? null,

                'subtotal' =>
                    $subtotal,

                'discount' =>
                    $discount,

                'tax' =>
                    $tax,

                'other_fee' =>
                    $otherFee,

                'grand_total' =>
                    $grandTotal,

                'paid_amount' =>
                    $paidAmount,

                'change_amount' =>
                    $changeAmount,

                'payment_method' =>
                    $validated['payment_method'],

                'status' =>
                    'completed',
            ]);


            /*
            |--------------------------------------------------------------------------
            | SIMPAN DETAIL TRANSAKSI
            |--------------------------------------------------------------------------
            */

            foreach ($validated['items'] as $item) {

                $productId = $item['product_id'];

                $qty = (int) $item['qty'];

                $product = $products[$productId];


                $itemSubtotal =
                    (float) $product->price * $qty;


                /*
                |--------------------------------------------------------------------------
                | SIMPAN DETAIL
                |--------------------------------------------------------------------------
                */

                $transaction->details()->create([

                    'product_id' =>
                        $product->id,

                    'product_code' =>
                        null,

                    'product_name' =>
                        $product->name,

                    'price' =>
                        $product->price,

                    'qty' =>
                        $qty,

                    'discount' =>
                        0,

                    'subtotal' =>
                        $itemSubtotal,
                ]);


                /*
                |--------------------------------------------------------------------------
                | KURANGI STOCK
                |--------------------------------------------------------------------------
                */

                $product->decrement(
                    'stock',
                    $qty
                );
            }


            /*
            |--------------------------------------------------------------------------
            | RETURN TRANSACTION
            |--------------------------------------------------------------------------
            */

            return $transaction;
        });


        /*
        |--------------------------------------------------------------------------
        | REDIRECT KE STRUK
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'transactions.receipt',
                $transaction
            )
            ->with(
                'success',
                'Transaksi berhasil disimpan.'
            );
    }


    /**
     * Menampilkan detail transaksi.
     */
    public function show(Transaction $transaction)
    {
        /*
        |--------------------------------------------------------------------------
        | LOAD RELATIONSHIP
        |--------------------------------------------------------------------------
        |
        | user           = kasir yang melakukan transaksi
        | details        = daftar produk dalam transaksi
        | details.product = data product asli
        |
        */

        $transaction->load([
            'user',
            'details.product',
        ]);
        /*
        |--------------------------------------------------------------------------
        | TAMPILKAN HALAMAN DETAIL
        |--------------------------------------------------------------------------
        */
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
        /*
        |--------------------------------------------------------------------------
        | LOAD RELATIONSHIP
        |--------------------------------------------------------------------------
        */

        $transaction->load([
            'user',
            'details.product',
        ]);
        /*
        |--------------------------------------------------------------------------
        | TAMPILKAN STRUK
        |--------------------------------------------------------------------------
        */
        return view(
            'kasir.receipt',
            compact('transaction')
        );
    }
}