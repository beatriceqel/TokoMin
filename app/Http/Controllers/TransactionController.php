<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Events\OrderPlaced; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
// Library Mike42 ESC/POS untuk pencetakan thermal otomatis (Acara 35 & 36)
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Exception;

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
     * Jika tunai langsung completed, jika non-tunai statusnya pending.
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
        $paymentMethod = $validated['payment_method'];

        $transaction = DB::transaction(function () use (
            $validated,
            $discount,
            $tax,
            $otherFee,
            $paidAmount,
            $paymentMethod
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

            if ($paymentMethod !== 'tunai') { 
                $paidAmount = $grandTotal;
            }
            
            if ($paidAmount < $grandTotal) {
                abort(422, 'Uang pembayaran tidak mencukupi.');
            }

            $changeAmount = $paidAmount - $grandTotal;

            $transactionNumber = 'TRX-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));

            // Status: 'completed' jika tunai, 'pending' jika non-tunai (menunggu pembayaran Midtrans)
            $status = ($paymentMethod === 'tunai') ? 'completed' : 'pending';

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
                'payment_method' => $paymentMethod,
                'status' => $status,
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

                // Stok hanya dikurangi langsung jika pembayaran TUNAI
                if ($paymentMethod === 'tunai') {
                    $product->decrement('stock', $qty);
                }
            }

            // TRIGGER BROADCAST EVENT HANYA JIKA TUNAI
            if ($paymentMethod === 'tunai') {
                event(new OrderPlaced($trx, auth()->user()));
            }

            return $trx;
        });

        // Cetak struk otomatis hanya jika TUNAI
        if ($transaction->status === 'completed') {
            $this->printThermalReceipt($transaction);
        }

        // Cek apakah request berasal dari AJAX/Fetch
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat.',
                'transaction' => $transaction
            ]);
        }

        return redirect()
            ->route('transactions.receipt', $transaction)
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    /**
     * Mengubah status transaksi menjadi completed (lunas) setelah pembayaran Midtrans sukses.
     */
    public function markAsPaid($id)
    {
        $transaction = Transaction::with('details.product')->findOrFail($id);

        if ($transaction->status === 'completed') {
            return response()->json(['success' => true]);
        }

        DB::transaction(function () use ($transaction) {
            $transaction->update(['status' => 'completed']);

            // Kurangi stok produk setelah pembayaran berhasil dikonfirmasi
            foreach ($transaction->details as $item) {
                if ($item->product) {
                    $item->product->decrement('stock', $item->qty);
                }
            }
        });

        // Cetak struk otomatis dan trigger broadcast setelah benar-benar lunas
        $this->printThermalReceipt($transaction);
        event(new OrderPlaced($transaction, $transaction->user));

        return response()->json([
            'success' => true, 
            'message' => 'Pembayaran berhasil dikonfirmasi dan lunas.'
        ]);
    }

    /**
     * Memproses pembayaran menggunakan Midtrans Snap API via HTTP Client Laravel.
     */
    public function payWithMidtrans(Transaction $transaction)
    {
        $serverKey = 'SB-Mid-server-jtrBluA7V7Mph5ouw4~4X1-u';
        $baseUrl = 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $orderIdForMidtrans = 'TOKOMINI-' . $transaction->id . '-' . time();
        $params = [
            'transaction_details' => [
                'order_id' => $orderIdForMidtrans,
                'gross_amount' => (int) $transaction->grand_total,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name ?? 'Kasir TokoMini',
                'email' => auth()->user()->email ?? 'kasir@tokomini.test',
            ],
        ];

        try {
            $authCredentials = base64_encode($serverKey . ':');
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $authCredentials,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ])
            ->withOptions(['verify' => false])
            ->post($baseUrl, $params);
            $responseData = $response->json();
            if ($response->successful() && isset($responseData['token'])) {
                return response()->json([
                    'success' => true,
                    'snapToken' => $responseData['token'],
                    'transaction' => $transaction
                ]);
            } else {
                Log::error('Midtrans HTTP Error Response:', [
                    'status' => $response->status(),
                    'body' => $responseData ?? $response->body()
                ]);

                $errorMessage = isset($responseData['error_messages'][0]) 
                    ? $responseData['error_messages'][0] 
                    : ('Gagal dari Midtrans: ' . json_encode($responseData));
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Midtrans Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fungsi helper untuk mencetak struk otomatis menggunakan Mike42 ESC/POS.
     */
    private function printThermalReceipt($transaction)
    {
        try {
            // Load relasi data yang dibutuhkan
            $transaction->load(['details', 'user']);

            // Sesuaikan nama printer thermal Anda yang sudah di-share di Windows
            $connector = new WindowsPrintConnector("POS80 Printer");
            $printer = new Printer($connector);
            
            // Header Struk TokoLokal
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("TokoLokal\n");
            $printer->text("Sabu Raijua - Nusa Tenggara Timur\n");
            $printer->text("--------------------------------\n");
            
            // Informasi Transaksi
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("No. TRX   : " . $transaction->transaction_number . "\n");
            $printer->text("Tanggal   : " . $transaction->created_at . "\n");
            $printer->text("Kasir     : " . ($transaction->user->name ?? 'Kasir') . "\n");
            $printer->text("--------------------------------\n");
            
            // Daftar Barang / Items
            foreach ($transaction->details as $item) {
                $printer->text($item->product_name . "\n");
                $printer->text($item->qty . " x Rp " . number_format($item->price, 0, ',', '.') . "   Rp " . number_format($item->subtotal, 0, ',', '.') . "\n");
            }
            
            $printer->text("--------------------------------\n");
            
            // Total & Pembayaran
            $printer->text("Subtotal  : Rp " . number_format((float)($transaction->subtotal ?? 0), 0, ',', '.') . "\n");
            if ($transaction->discount > 0) {
                $printer->text("Diskon    : Rp " . number_format((float)($transaction->discount ?? 0), 0, ',', '.') . "\n");
            }
            if ($transaction->tax > 0) {
                $printer->text("Pajak     : Rp " . number_format((float)($transaction->tax ?? 0), 0, ',', '.') . "\n");
            }
            if ($transaction->other_fee > 0) {
                $printer->text("Biaya Lain: Rp " . number_format((float)($transaction->other_fee ?? 0), 0, ',', '.') . "\n");
            }
            $printer->text("TOTAL     : Rp " . number_format((float)($transaction->grand_total ?? 0), 0, ',', '.') . "\n");
            $printer->text("Bayar     : Rp " . number_format((float)($transaction->paid_amount ?? 0), 0, ',', '.') . "\n");
            $printer->text("Kembalian : Rp " . number_format((float)($transaction->change_amount ?? 0), 0, ',', '.') . "\n");
            $printer->text("--------------------------------\n");
            
            // Footer
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Transaksi Berhasil!\n");
            $printer->text("Terima kasih telah berbelanja.\n");
            $printer->text("Semoga hari Anda menyenangkan.\n\n");
            
            $printer->cut();
            $printer->close();

        } catch (Exception $e) {
            // Catat ke log jika printer offline/tidak terjangkau
            Log::error("Gagal mencetak struk thermal otomatis: " . $e->getMessage());
        }
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