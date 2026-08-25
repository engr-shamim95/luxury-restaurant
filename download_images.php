<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Setting;

$products = Product::all();

foreach ($products as $product) {
    if (str_starts_with($product->image, 'http')) {
        echo "Downloading for {$product->slug}...\n";
        
        // Setup context to mock a browser, Unsplash blocks some bots
        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\r\n"
            ]
        ]);
        
        $imageContent = @file_get_contents($product->image, false, $context);
        
        if ($imageContent) {
            $filename = 'products/' . $product->slug . '.jpg';
            Storage::disk('public')->put($filename, $imageContent);
            $product->update(['image' => $filename]);
            echo "Saved {$filename}\n";
        } else {
            echo "Failed to download {$product->image}\n";
            // Fallback placeholder
            $product->update(['image' => null]);
        }
    }
}

// Download logo
echo "Downloading logo...\n";
$logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a7/React-icon.svg/512px-React-icon.svg.png'; // simple placeholder if logoipsum fails
// Let's use a nice restaurant logo placeholder
$logoUrl = 'https://placehold.co/400x100/f59e0b/ffffff/png?text=Bella+Vista';
$context = stream_context_create(['http' => ['header' => "User-Agent: Mozilla/5.0\r\n"]]);
$logoContent = @file_get_contents($logoUrl, false, $context);
if ($logoContent) {
    Storage::disk('public')->put('settings/logo.png', $logoContent);
    Setting::updateOrCreate(['key' => 'site_logo'], ['value' => 'settings/logo.png', 'type' => 'string']);
    echo "Logo saved.\n";
}

echo "Done downloading images!\n";
