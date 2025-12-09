<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\MeasurementUnit;
use App\Models\Recipient;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class OutTransactionController extends Controller
{
    public function index()
    {
        return Inertia::render('OutboundItem', [
            'items' => Item::all(),
            'measurementUnits' => MeasurementUnit::all(),
            'recipients' => Recipient::all(),
        ]);
    }

    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'recipient_id' => 'required|exists:recipients,id',
            'division' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
            'transaction_details' => 'required|array|min:1',
            'transaction_details.*.item_id' => 'required|exists:items,id',
            'transaction_details.*.unit_id' => 'required|exists:measurement_units,id',
            'transaction_details.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Cek stok sebelum proses transaksi
            foreach ($validated['transaction_details'] as $index => $detail) {
                $stock = Stock::where('item_id', $detail['item_id'])
                    ->where('unit_id', $detail['unit_id'])
                    ->first();

                // Ambil nama item untuk pesan error yang lebih jelas
                $item = Item::find($detail['item_id']);
                $unit = MeasurementUnit::find($detail['unit_id']);

                if (!$stock) {
                    throw new \Exception("Stok untuk barang '{$item->name}' dengan satuan '{$unit->name}' tidak ditemukan.");
                }

                if ($stock->quantity < $detail['quantity']) {
                    throw new \Exception(
                        "Stok tidak mencukupi untuk barang '{$item->name}'. " .
                        "Stok tersedia: {$stock->quantity} {$unit->name}, " .
                        "Diminta: {$detail['quantity']} {$unit->name}"
                    );
                }
            }

            // Buat transaksi header
            $transaction = Transaction::create([
                'type' => 'out',
                'recipient_id' => $validated['recipient_id'],
                'division' => $validated['division'] ?? null,
                'transaction_date' => $validated['transaction_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Buat transaction details dan update stok
            foreach ($validated['transaction_details'] as $detail) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'item_id' => $detail['item_id'],
                    'unit_id' => $detail['unit_id'],
                    'quantity' => $detail['quantity'],
                ]);

                // Update stok barang (kurangi stok untuk barang keluar)
                $stock = Stock::where('item_id', $detail['item_id'])
                    ->where('unit_id', $detail['unit_id'])
                    ->first();

                $stock->decrement('quantity', $detail['quantity']);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Transaksi barang keluar berhasil disimpan');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
}