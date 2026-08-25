<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Setting;

Setting::updateOrCreate(['key' => 'site_logo'], ['value' => 'settings/logo.svg', 'type' => 'string']);
echo "Updated site logo to settings/logo.svg\n";

$missing = [
    'crispy-calamari-fritti' => 'https://upload.wikimedia.org/wikipedia/commons/4/4b/Fried_calamari.jpg',
    'spaghetti-alla-carbonara' => 'https://upload.wikimedia.org/wikipedia/commons/3/33/Espaguetis_carbonara.jpg',
    'classic-tiramisu' => 'https://upload.wikimedia.org/wikipedia/commons/f/fc/Tiramisu_with_blueberries_and_raspberries%2C_2022.jpg',
    'sicilian-cannoli' => 'https://upload.wikimedia.org/wikipedia/commons/9/91/Cannoli_in_Palermo.jpg',
    'san-pellegrino-750ml' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ca/San_pellegrino.jpg/800px-San_pellegrino.jpg'
];

$context = stream_context_create(['http' => ['header' => "User-Agent: Mozilla/5.0\r\n"]]);

foreach ($missing as $slug => $url) {
    echo "Downloading {$slug}...\n";
    $content = @file_get_contents($url, false, $context);
    if ($content) {
        $path = "products/{$slug}.jpg";
        Storage::disk('public')->put($path, $content);
        Product::where('slug', $slug)->update(['image' => $path]);
        echo "Saved {$path}\n";
    } else {
        echo "Failed to download {$slug}\n";
    }
}
echo "Done!\n";
