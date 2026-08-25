<?php

$dir = __DIR__ . '/database/migrations';
$files = scandir($dir);

$replacements = [
    'create_settings_table' => <<<PHP
            \$table->id();
            \$table->string('key')->unique();
            \$table->text('value')->nullable();
            \$table->string('type')->default('string');
            \$table->timestamps();
PHP,
    'create_pages_table' => <<<PHP
            \$table->id();
            \$table->string('title');
            \$table->string('slug')->unique();
            \$table->longText('content')->nullable();
            \$table->string('meta_title')->nullable();
            \$table->text('meta_description')->nullable();
            \$table->string('og_image')->nullable();
            \$table->boolean('is_published')->default(true);
            \$table->timestamps();
PHP,
    'create_navigation_menus_table' => <<<PHP
            \$table->id();
            \$table->string('name');
            \$table->string('location')->unique();
            \$table->timestamps();
PHP,
    'create_navigation_items_table' => <<<PHP
            \$table->id();
            \$table->foreignId('navigation_menu_id')->constrained()->onDelete('cascade');
            \$table->string('label');
            \$table->string('url')->nullable();
            \$table->foreignId('page_id')->nullable()->constrained()->onDelete('set null');
            \$table->integer('order')->default(0);
            \$table->string('target')->default('_self');
            \$table->timestamps();
PHP,
    'create_categories_table' => <<<PHP
            \$table->id();
            \$table->string('name');
            \$table->string('slug')->unique();
            \$table->string('image')->nullable();
            \$table->text('description')->nullable();
            \$table->boolean('is_active')->default(true);
            \$table->integer('order')->default(0);
            \$table->timestamps();
PHP,
    'create_products_table' => <<<PHP
            \$table->id();
            \$table->foreignId('category_id')->constrained()->onDelete('cascade');
            \$table->string('name');
            \$table->string('slug')->unique();
            \$table->text('description')->nullable();
            \$table->decimal('base_price', 10, 2);
            \$table->string('image')->nullable();
            \$table->boolean('is_available')->default(true);
            \$table->boolean('has_variants')->default(false);
            \$table->timestamps();
PHP,
    'create_product_variants_table' => <<<PHP
            \$table->id();
            \$table->foreignId('product_id')->constrained()->onDelete('cascade');
            \$table->string('name');
            \$table->string('type')->default('size');
            \$table->decimal('price_adjustment', 10, 2)->default(0);
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
PHP,
    'create_orders_table' => <<<PHP
            \$table->id();
            \$table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            \$table->string('customer_name');
            \$table->string('customer_email');
            \$table->string('customer_phone')->nullable();
            \$table->string('order_type')->default('pickup');
            \$table->text('delivery_address')->nullable();
            \$table->text('order_notes')->nullable();
            \$table->decimal('subtotal', 10, 2);
            \$table->decimal('tax', 10, 2)->default(0);
            \$table->decimal('total', 10, 2);
            \$table->string('payment_method');
            \$table->string('payment_status')->default('pending');
            \$table->string('order_status')->default('new');
            \$table->string('transaction_id')->nullable();
            \$table->timestamps();
PHP,
    'create_order_items_table' => <<<PHP
            \$table->id();
            \$table->foreignId('order_id')->constrained()->onDelete('cascade');
            \$table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            \$table->string('product_name');
            \$table->integer('quantity');
            \$table->decimal('unit_price', 10, 2);
            \$table->json('variants_selected')->nullable();
            \$table->decimal('total_price', 10, 2);
            \$table->timestamps();
PHP,
];

foreach ($files as $file) {
    if (str_ends_with($file, '.php')) {
        $path = $dir . '/' . $file;
        $content = file_get_contents($path);
        
        if (str_contains($file, 'create_users_table')) {
            // Add is_admin to users
            $content = str_replace(
                "\$table->rememberToken();",
                "\$table->boolean('is_admin')->default(false);\n            \$table->rememberToken();",
                $content
            );
            file_put_contents($path, $content);
            echo "Updated \$file\n";
            continue;
        }

        foreach ($replacements as $key => $replacement) {
            if (str_contains($file, $key)) {
                $search = "\$table->id();\n            \$table->timestamps();";
                $content = str_replace($search, $replacement, $content);
                file_put_contents($path, $content);
                echo "Updated \$file\n";
                break;
            }
        }
    }
}
