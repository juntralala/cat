<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index() {
        return Inertia::render('Setting');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'nullable|string|max:255',
            'app_icon' => 'nullable|image|max:2048',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone_number' => 'nullable|string|max:20',
        ]);

        // Loop through validated data dan simpan ke database
        foreach ($validated as $key => $value) {
            // Skip icon dulu, handle terpisah
            if ($key === 'app_icon' && $value !== null) {
                // Upload icon
                $path = $value->store('icons', 'public');
                
                // Hapus icon lama jika ada
                $oldIcon = Setting::where('key', 'app_icon')->first();
                if ($oldIcon && $oldIcon->value) {
                    Storage::disk('public')->delete($oldIcon->value);
                }
                
                Setting::updateOrCreate(
                    ['key' => 'app_icon'],
                    ['value' => "/storage/$path"]
                );
                continue;
            }

            // Update atau create setting lainnya
            if ($value !== null) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        }

        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}
