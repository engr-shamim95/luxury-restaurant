<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display the settings management view.
     */
    public function index(): View
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update dynamic application and restaurant settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $booleanKeys = [
            'enable_pickup',
            'enable_delivery',
            'cod_enabled',
            'stripe_enabled',
            'square_enabled',
            'merchant_enabled',
        ];

        $numericKeys = [
            'tax_rate' => 'float',
            'tax_rate_percent' => 'float',
            'delivery_fee' => 'float',
            'minimum_order_amount' => 'float',
        ];

        // Handle file uploads (e.g. logo and favicon)
        if ($request->hasFile('site_logo')) {
            $request->validate(['site_logo' => 'image|mimes:jpeg,png,jpg,webp,svg|max:2048']);
            $oldLogo = Setting::get('site_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $logoPath = $request->file('site_logo')->store('settings', 'public');
            Setting::set('site_logo', $logoPath, 'image');
        }

        if ($request->hasFile('site_favicon')) {
            $request->validate(['site_favicon' => 'image|mimes:jpeg,png,jpg,ico|max:1024']);
            $oldFavicon = Setting::get('site_favicon');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            $faviconPath = $request->file('site_favicon')->store('settings', 'public');
            Setting::set('site_favicon', $faviconPath, 'image');
        }

        // Process all input fields
        $inputs = $request->except(['_token', '_method', 'site_logo', 'site_favicon']);

        foreach ($inputs as $key => $value) {
            $type = 'string';
            if (isset($numericKeys[$key])) {
                $type = $numericKeys[$key];
            } elseif (in_array($key, $booleanKeys, true)) {
                $type = 'boolean';
                $value = $value ? '1' : '0';
            }

            Setting::set($key, (string) $value, $type);

            // Keep alias sync (e.g., site_name <=> restaurant_name)
            if ($key === 'site_name') {
                Setting::set('restaurant_name', (string) $value, 'string');
            } elseif ($key === 'restaurant_name') {
                Setting::set('site_name', (string) $value, 'string');
            } elseif ($key === 'contact_email') {
                Setting::set('restaurant_email', (string) $value, 'string');
            } elseif ($key === 'contact_phone') {
                Setting::set('restaurant_phone', (string) $value, 'string');
            } elseif ($key === 'contact_address') {
                Setting::set('restaurant_address', (string) $value, 'string');
            } elseif ($key === 'tax_rate') {
                Setting::set('tax_rate_percent', (string) $value, 'float');
            }
        }

        // Ensure unchecked checkboxes are saved as 0
        foreach ($booleanKeys as $boolKey) {
            if (! $request->has($boolKey)) {
                Setting::set($boolKey, '0', 'boolean');
            }
        }

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully!');
    }
}
