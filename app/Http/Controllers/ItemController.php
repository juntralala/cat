<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\MeasurementUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function validateItem(Request $request, $itemId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::when($itemId == null, "unique:items,name")],
            'base_measurement_unit_id' => ['required', 'string', 'exists:measurement_units,id'],
        ]);
    }

    public function page()
    {
        return Inertia::render('Item', [
            'items' => Item::with('baseMeasurementUnit')->get(),
            'baseUnits' => MeasurementUnit::where('is_base', '=', true)->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateItem($request);
        Item::create($validated);
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateItem($request, $id);
        $item = Item::findOrFail($id);
        $item->update($validated);
        return redirect()->back();
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->back();
    }
}
