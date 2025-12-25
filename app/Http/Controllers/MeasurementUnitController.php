<?php

namespace App\Http\Controllers;

use App\Models\MeasurementUnit;
use App\Models\Sku;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MeasurementUnitController extends Controller
{
    private function validateUnit(Request $request) {
        return $request->validate([
            'name' => 'required|string|max:255|unique:measurement_units,name',
            'is_base' => 'required|boolean',
            'base_measurement_unit_id' => 'exclude_if:is_base,true|nullable|exists:measurement_units,id',
            'conversion' => 'exclude_if:is_base,true|exclude_if:base_measurement_unit_id,null|numeric|min:1',
        ], [
            'name.required' => 'Nama satuan tidak boleh kosong',
            'name.string' => 'Tipe nama satuan data harus teks',
            'name.max' => 'Nama satuan tidak boleh lebih panjang dari 255 karakter',
            'name.unique' => 'Nama satuan ":input" sudah digunakan, gunakan nama lain',
            'is_base.required' => 'Adalah base unit harus diisi',
            'is_base.boolean' => 'Adalah base unit harus boolean, ":input" diterima',
        ]);
    }

    public function page() {
        $units = MeasurementUnit::all();
        return Inertia::render('Unit', ['units' => $units]);
    }

    public function store(Request $request) {
        $validated = $this->validateUnit($request);
        
        MeasurementUnit::create($validated);
        return redirect()->back();
    }

    public function update(Request $request, $id) {
        $validated = $this->validateUnit($request);
        
        MeasurementUnit::find($id)->update($validated);
        return redirect()->back();
    }

    public function destroy($id) {
        $unit = MeasurementUnit::findOrFail($id);
        $transaction = $unit->transactionItems()->withTrashed()->exists();
        $item = $unit->basedMeasurementUnitItems()->withTrashed()->exists();
        $skuUnitConversion = $unit->skuMeasurementUnitConversions()->withTrashed()->exists();
        if(!($transaction && $item && $skuUnitConversion)) {
            $unit->forceDelete();
        } else {
            $unit->delete();
        }
        return redirect()->back();
    }

    // belum ada route
    public function restore($id) {

        $unit = MeasurementUnit::findOrFail($id);
        $unit->restore();
        return redirect()->back();
    }

    public function getSupportedMueasurementUnitsBySkuId(Request $request, $skuId) {
        $sku = Sku::with("skuMeasurementUnitConversions")
            ->findOrFail($skuId, ['id', 'item_id']);
        $baseMeasurementUnit = $sku->item
            ->baseMeasurementUnit()
            ->with('derivedMeasurementUnits')
            ->first();
        $allMeasurementUnits = collect([$baseMeasurementUnit])
            ->merge($baseMeasurementUnit->derivedMeasurementUnits)
            ->merge($sku->skuMeasurementUnitConversions->map(function($conversion) {
                return [
                    'id' => $conversion->measurement_unit_id,
                    'name'=> $conversion->unit->name,
                    'conversion' => $conversion->conversion
                ];
            }))
            ->values();
        return response()->json([
            'data' => $allMeasurementUnits
        ]);
    }
}
