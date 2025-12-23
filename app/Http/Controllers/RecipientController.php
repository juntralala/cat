<?php

namespace App\Http\Controllers;

use App\Models\Recipient;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecipientController extends Controller
{

    private function validateRecipient(Request $request)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'division' => 'nullable|string|max:100',
        ]);
    }

    public function page()
    {
        return Inertia::render('Recipient', [
            'recipients' => Recipient::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRecipient($request);
        Recipient::create($validated);
        return redirect()->route('recipients')->with('success', 'Recipient created.');
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateRecipient($request);
        $recipient = Recipient::findOrFail($id);
        $recipient->update($validated);
        return redirect()->route('recipients')->with('success', 'Recipient updated.');
    }

    public function destroy($id)
    {
        $recipient = Recipient::findOrFail($id);
        $recipient->delete();
        return redirect()->route('recipients')->with('success', 'Recipient deleted.');
    }
}
