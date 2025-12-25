<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\MeasurementUnit;
use App\Models\Sku;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InTransactionController extends Controller
{

    public function index()
    {
        return Inertia::render('InboundItem', [
            'items' => Item::with('skus')->get(),
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
            'transaction_items' => 'required|array|min:1',
            'transaction_items.*.sku_id' => 'required|exists:skus,id',
            'transaction_items.*.unit_id' => 'required|exists:measurement_units,id',
            'transaction_items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'type' => $validated['type'],
                'supplier' => $validated['supplier'],
                'recipient_id' => null, // null untuk barang masuk
                'transaction_date' => Date::parse($validated['transaction_date']),
                'notes' => $validated['notes'],
            ]);

            foreach ($validated['transaction_items'] as $item) {
                $sku = Sku::findOrFail($item['sku_id']);
                $transactionItem = TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'sku_id' => $item['sku_id'],
                    'measurement_unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'price' => $sku->price,
                ]);
                $unit = $transactionItem->unit;
                if($unit->is_base) {
                    $sku->increment('quantity', $item['quantity']);
                } else if ($unit->baseMeasurementUnit()->withTrashed()->exists()) {
                    $sku->increment('quantity', $item['quantity'] * ($unit->conversion ?? 1));
                } else if($skuMeasurementUnitConversion = $unit->skuMeasurementUnitConversions()->where('sku_id',$item['sku_id'])->first(['conversion'])) {
                    $conversion = $skuMeasurementUnitConversion->conversion ?? 1;
                    $sku->increment('quantity', $conversion * $item['quantity']);
                } else {
                    abort(400, "Ukuran satuan bukan satuan dasar dan tidak memiliki konversi yang terdaftar");
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