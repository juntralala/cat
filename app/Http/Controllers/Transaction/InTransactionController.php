<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\MeasurementUnit;
use App\Models\Sku;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Service\MeasurementUnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InTransactionController extends Controller
{
    public function __construct(
        private MeasurementUnitService $measurementUnitService
    ){}

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
                'user_id' => $request->user()->id,
                'supplier' => $validated['supplier'],
                'recipient_id' => null, // null untuk barang masuk
                'transaction_date' => Date::parse($validated['transaction_date']),
                'notes' => $validated['notes'],
            ]);

            foreach ($validated['transaction_items'] as $item) {
                $sku = Sku::findOrFail($item['sku_id']);
                $conversion = $this->measurementUnitService->getConversion($item['unit_id'], $sku->id);
                $baseQuantity = $item['quantity'] * $conversion;
                $sku->increment('quantity',  $baseQuantity);
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'sku_id' => $item['sku_id'],
                    'measurement_unit_id' => $item['unit_id'],
                    'price' => $sku->price,
                    'quantity' => $item['quantity'],
                    'base_quantity' => $baseQuantity,
                ]);
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