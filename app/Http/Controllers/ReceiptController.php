<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Transaction; // Sesuaikan dengan model transaksi di project kamu
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Exception;
class ReceiptController extends Controller
{
    public function print($id)
    {
        try {
            $transaction = Transaction::with('details.product')->findOrFail($id);
            $connector = new WindowsPrintConnector("POS80 Printer");
            $printer = new Printer($connector);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("TokoMini\n");
            $printer->text("Sabu Raijua - Nusa Tenggara Timur\n");
            $printer->text("--------------------------------\n");

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("No. Transaksi : " . $transaction->invoice_code . "\n"); 
            $printer->text("Tanggal       : " . $transaction->created_at . "\n");
            $printer->text("Kasir         : " . ($transaction->cashier->name ?? 'Kasir 1') . "\n");
            $printer->text("--------------------------------\n");
            foreach ($transaction->details as $item) {
                $printer->text($item->product->name . "\n");
                $printer->text($item->quantity . " x Rp " . number_format($item->price, 0, ',', '.') . "   Rp " . number_format($item->subtotal, 0, ',', '.') . "\n");
            }
            $printer->text("--------------------------------\n");
            $printer->text("TOTAL         : Rp " . number_format((float)($transaction->total_amount ?? 0), 0, ',', '.') . "\n");
            $printer->text("Bayar         : Rp " . number_format((float)($transaction->paid_amount ?? 0), 0, ',', '.') . "\n");
            $printer->text("Kembalian     : Rp " . number_format((float)($transaction->change_amount ?? 0), 0, ',', '.') . "\n");
            $printer->text("--------------------------------\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Transaksi berhasil!\n");
            $printer->text("Terima kasih telah berbelanja.\n");
            $printer->text("Semoga hari Anda menyenangkan.\n\n");
            $printer->cut();
            $printer->close();
            return response()->json(['message' => 'Struk berhasil dicetak ke printer thermal!']);
            
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}