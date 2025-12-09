<?php

namespace App\Http\Controllers;

use App\Models\MeasurementUnit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MeasurementUnitController extends Controller
{
    private function validateUnit(Request $request) {
        return $request->validate([
            'name' => 'required|string|max:255|unique:measurement_units,name',
        ],[
            'name.required' => 'Nama satuan tidak boleh kosong',
            'name.string' => 'Tipe nama satuan data harus teks',
            'name.max' => 'Nama satuan tidak boleh lebih panjang dari 255 karakter',
            'name.unique' => 'Nama satuan ":input" sudah digunakan, gunakan nama lain',
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
        $stock = $unit->stocks()->first();
        $transaction = $unit->transactionDetails()->first();
        if(!($stock && $transaction)) {
            $unit->forceDelete();
        } else {
            $unit->delete();
        }
        return redirect()->back();
    }

    // belum ada route
    public function restore($id) {

        $unit = MeasurementUnit::findOrFail($id);
        $unit->deleted_at = null;
        $unit->save();
        return redirect()->back();
    }
}
