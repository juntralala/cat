<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Item;
use App\Models\MeasurementUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class StockController extends Controller
{
    private function validateStock(Request $request) {
        return $request->validate([
            'item_id' => 'required|exists:items,id',
            'unit_id' => 'required|exists:measurement_units,id',
            'quantity' => 'required|integer|min:0',
        ], [
            'item_id.required' => 'Barang harus dipilih',
            'item_id.exists' => 'Barang tidak ditemukan',
            'unit_id.required' => 'Unit ukuran harus dipilih',
            'unit_id.exists' => 'Unit ukuran tidak ditemukan',
            'quantity.required' => 'Jumlah harus diisi',
            'quantity.integer' => 'Jumlah harus berupa angka bulat',
            'quantity.min' => 'Jumlah harus setidaknya 0',
        ]);
    }

    public function index()
    {
        $stocks = Stock::with(['item', 'unit'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $items = Item::orderBy('name', 'asc')->get();
        $units = MeasurementUnit::orderBy('name', 'asc')->get();
        
        return Inertia::render('Stock', [
            'stocks' => $stocks,
            'items' => $items,
            'units' => $units
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated =$this->validateStock($request);

        $stock = new Stock();
        $stock->id = Str::uuid();
        $stock->item_id = $validated['item_id'];
        $stock->unit_id = $validated['unit_id'];
        $stock->quantity = $validated['quantity'];
        $stock->save();

        return redirect()->back()->with('success', 'Stok berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $stock = Stock::findOrFail($id);

        $validated = $this->validateStock($request);

        $stock->item_id = $validated['item_id'];
        $stock->unit_id = $validated['unit_id'];
        $stock->quantity = $validated['quantity'];
        $stock->save();

        return redirect()->back()->with('success', 'Stok berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $stock = Stock::findOrFail($id);
        $stock->delete();

        return redirect()->back()->with('success', 'Stok berhasil dihapus');
    }
}