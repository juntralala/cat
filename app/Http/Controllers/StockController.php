<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Item;
use App\Models\MeasurementUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StockController extends Controller
{
    private function validateStock(Request $request)
    {
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

    public function store(Request $request)
    {
        $validated = $this->validateStock($request);

        $stock = new Stock();
        $stock->item_id = $validated['item_id'];
        $stock->unit_id = $validated['unit_id'];
        $stock->quantity = $validated['quantity'];
        $stock->save();

        return redirect()->back()->with('success', 'Stok berhasil ditambahkan');
    }

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

    public function destroy(string $id)
    {
        $stock = Stock::findOrFail($id);
        $stock->delete();

        return redirect()->back()->with('success', 'Stok berhasil dihapus');
    }

    public function toXlsx(Request $request)
    {
        $stocks = Stock::all();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // header
        $sheet->fromArray(source: [
            'No',
            'Nama Barang',
            'Satuan',
            'Jumlah'
        ], nullValue: null, startCell: 'A1');

        for ($i = 0; $i < $stocks->count(); $i++) {
            $stock = $stocks->get($i);
            $sheet->fromArray(
                [
                    $i + 1,
                    $stock->item->name,
                    $stock->unit->name,
                    $stock->quantity
                ],
                nullValue: null,
                startCell: 'A' . ($i + 2)
            );
        }
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, "stok_barang_" . Date::now()->format("d_m_Y_i_s") . ".xlsx");
    }
}