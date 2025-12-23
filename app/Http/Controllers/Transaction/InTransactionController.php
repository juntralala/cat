<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\MeasurementUnit;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InTransactionController extends Controller
{

    public function index()
    {
        return Inertia::render('InboundItem', [
            'items' => Item::all(),
            'measurementUnits' => MeasurementUnit::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:in',
            'supplier' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
            'transaction_details' => 'required|array|min:1',
            'transaction_details.*.item_id' => 'required|exists:items,id',
            'transaction_details.*.unit_id' => 'required|exists:measurement_units,id',
            'transaction_details.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // 1. Buat Transaction Header
            $transaction = Transaction::create([
                'type' => $validated['type'],
                'supplier' => $validated['supplier'],
                'recipient_id' => null, // null untuk barang masuk
                'transaction_date' => $validated['transaction_date'],
                'notes' => $validated['notes'],
            ]);

            // 2. Buat Transaction Details & Update Stock
            foreach ($validated['transaction_details'] as $detail) {
                // Simpan detail transaksi
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'item_id' => $detail['item_id'],
                    'unit_id' => $detail['unit_id'],
                    'quantity' => $detail['quantity'],
                ]);

                // Update atau buat stock
                $stock = Stock::where('item_id', $detail['item_id'])
                    ->where('unit_id', $detail['unit_id'])
                    ->first();

                if ($stock) {
                    // Update stock yang sudah ada
                    $stock->increment('quantity', amount: $detail['quantity']);
                } else {
                    // Buat stock baru
                    Stock::create([
                        'item_id' => $detail['item_id'],
                        'unit_id' => $detail['unit_id'],
                        'quantity' => $detail['quantity'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Transaksi barang masuk berhasil disimpan');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Gagal menyimpan transaksi: ' . $e->getMessage()])
                ->withInput();
        }
    }
}