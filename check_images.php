<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = App\Models\Product::all();
foreach($products as $p) {
    echo $p->slug . ': ' . $p->image . "\n";
}
