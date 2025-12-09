<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function validateItem(Request $request)
    {
        return $request->validate([
            'name' => 'required|string|max:255'
        ]);
    }

    public function page()
    {
        return Inertia::render('Item', [
            'items' => Item::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateItem($request);
        Item::create($validated);
        return redirect()->back();
    }

    public function update(Request $request, Item $item)
    {
        $validated = $this->validateItem($request);
        $item->update($validated);
        return redirect()->back();
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->back();
    }
}
