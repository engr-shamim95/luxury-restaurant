<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\NavigationItem;
use App\Models\NavigationMenu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Admin and Customer Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Restaurant Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );

        // 2. Seed Restaurant Settings
        $settings = [
            'site_name' => ['value' => 'Bella Vista Ristorante', 'type' => 'string'],
            'restaurant_name' => ['value' => 'Bella Vista Ristorante', 'type' => 'string'],
            'site_tagline' => ['value' => 'Authentic Wood-Fired Italian Cuisine & Artisan Pasta', 'type' => 'string'],
            'hero_title' => ['value' => 'Taste the Authentic Essence of Italy', 'type' => 'string'],
            'hero_subtitle' => ['value' => 'Handcrafted pasta, wood-fired pizzas, and fresh seasonal ingredients made daily.', 'type' => 'string'],
            'contact_email' => ['value' => 'info@bellavistarestaurant.com', 'type' => 'string'],
            'restaurant_email' => ['value' => 'info@bellavistarestaurant.com', 'type' => 'string'],
            'contact_phone' => ['value' => '+1 (555) 789-2345', 'type' => 'string'],
            'restaurant_phone' => ['value' => '+1 (555) 789-2345', 'type' => 'string'],
            'contact_address' => ['value' => '456 Culinary Boulevard, Gourmet District, NY 10012', 'type' => 'string'],
            'restaurant_address' => ['value' => '456 Culinary Boulevard, Gourmet District, NY 10012', 'type' => 'string'],
            'opening_hours' => ['value' => 'Mon - Thu: 11:30 AM - 10:00 PM, Fri - Sun: 11:00 AM - 11:00 PM', 'type' => 'string'],
            'currency_symbol' => ['value' => '$', 'type' => 'string'],
            'currency_code' => ['value' => 'USD', 'type' => 'string'],
            'tax_rate' => ['value' => '8.5', 'type' => 'float'],
            'tax_rate_percent' => ['value' => '8.5', 'type' => 'float'],
            'delivery_fee' => ['value' => '4.99', 'type' => 'float'],
            'minimum_order_amount' => ['value' => '15.00', 'type' => 'float'],
            'enable_pickup' => ['value' => '1', 'type' => 'boolean'],
            'enable_delivery' => ['value' => '1', 'type' => 'boolean'],
            'facebook_url' => ['value' => 'https://facebook.com/bellavista', 'type' => 'string'],
            'instagram_url' => ['value' => 'https://instagram.com/bellavista', 'type' => 'string'],
            'twitter_url' => ['value' => 'https://twitter.com/bellavista', 'type' => 'string'],
            'cod_enabled' => ['value' => '1', 'type' => 'boolean'],
            'stripe_enabled' => ['value' => '0', 'type' => 'boolean'],
        ];

        foreach ($settings as $key => $data) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $data['value'], 'type' => $data['type']]
            );
        }

        // 3. Seed CMS Pages
        $aboutPage = Page::updateOrCreate(
            ['slug' => 'about-us'],
            [
                'title' => 'About Us',
                'content' => '<h2>Welcome to Bella Vista Ristorante</h2><p>Founded in 2012, Bella Vista brings the heartfelt warmth and culinary artistry of traditional Italian cooking straight to your table. Our master chefs prepare fresh handmade pasta daily and bake our pizzas in authentic stone-hearth wood-fired ovens imported directly from Naples.</p><p>We believe in honest ingredients, locally-sourced farm produce, and timeless Mediterranean recipes passed down through generations.</p>',
                'meta_title' => 'About Bella Vista Ristorante - Authentic Italian Dining',
                'meta_description' => 'Discover our story, heritage, and passion for wood-fired pizzas and handmade pastas.',
                'is_published' => true,
            ]
        );

        $contactPage = Page::updateOrCreate(
            ['slug' => 'contact-us'],
            [
                'title' => 'Contact Us',
                'content' => '<h2>Get in Touch</h2><p>We are delighted to welcome you for dine-in, takeaway, or special private events. Reach out with any reservations, feedback, or catering inquiries.</p><p><strong>Address:</strong> 456 Culinary Boulevard, Gourmet District, NY 10012</p><p><strong>Phone:</strong> +1 (555) 789-2345</p><p><strong>Email:</strong> info@bellavistarestaurant.com</p>',
                'meta_title' => 'Contact & Location - Bella Vista Ristorante',
                'meta_description' => 'Visit us or get in touch for reservations, takeout orders, and private events.',
                'is_published' => true,
            ]
        );

        $termsPage = Page::updateOrCreate(
            ['slug' => 'terms-conditions'],
            [
                'title' => 'Terms & Conditions',
                'content' => '<h2>Terms of Service</h2><p>By placing an order through Bella Vista Ristorante, you agree to our ordering and payment policies. Delivery times are estimates and may vary based on weather and traffic conditions. All online and cash-on-delivery payments are final once food preparation begins.</p>',
                'meta_title' => 'Terms and Conditions - Bella Vista Ristorante',
                'meta_description' => 'Read our terms of service and online ordering conditions.',
                'is_published' => true,
            ]
        );

        $privacyPage = Page::updateOrCreate(
            ['slug' => 'privacy-policy'],
            [
                'title' => 'Privacy Policy',
                'content' => '<h2>Privacy Policy</h2><p>At Bella Vista Ristorante, your privacy is paramount. We only collect customer details necessary to fulfill your meal preparation, delivery orders, and loyalty communications. We never share or sell customer data to third-party advertisers.</p>',
                'meta_title' => 'Privacy Policy - Bella Vista Ristorante',
                'meta_description' => 'Learn how we protect and handle your personal information.',
                'is_published' => true,
            ]
        );

        // 4. Seed Navigation Menus & Items
        $headerMenu = NavigationMenu::updateOrCreate(
            ['location' => 'header'],
            ['name' => 'Main Header Navigation']
        );

        $headerMenu->items()->delete();
        NavigationItem::create([
            'navigation_menu_id' => $headerMenu->id,
            'label' => 'Home',
            'url' => '/',
            'order' => 1,
            'target' => '_self',
        ]);
        NavigationItem::create([
            'navigation_menu_id' => $headerMenu->id,
            'label' => 'Menu',
            'url' => '/menu',
            'order' => 2,
            'target' => '_self',
        ]);
        NavigationItem::create([
            'navigation_menu_id' => $headerMenu->id,
            'label' => 'About Us',
            'url' => null,
            'page_id' => $aboutPage->id,
            'order' => 3,
            'target' => '_self',
        ]);
        NavigationItem::create([
            'navigation_menu_id' => $headerMenu->id,
            'label' => 'Contact',
            'url' => null,
            'page_id' => $contactPage->id,
            'order' => 4,
            'target' => '_self',
        ]);

        $footerMenu = NavigationMenu::updateOrCreate(
            ['location' => 'footer'],
            ['name' => 'Footer Navigation']
        );

        $footerMenu->items()->delete();
        NavigationItem::create([
            'navigation_menu_id' => $footerMenu->id,
            'label' => 'Menu',
            'url' => '/menu',
            'order' => 1,
            'target' => '_self',
        ]);
        NavigationItem::create([
            'navigation_menu_id' => $footerMenu->id,
            'label' => 'About Us',
            'url' => null,
            'page_id' => $aboutPage->id,
            'order' => 2,
            'target' => '_self',
        ]);
        NavigationItem::create([
            'navigation_menu_id' => $footerMenu->id,
            'label' => 'Terms & Conditions',
            'url' => null,
            'page_id' => $termsPage->id,
            'order' => 3,
            'target' => '_self',
        ]);
        NavigationItem::create([
            'navigation_menu_id' => $footerMenu->id,
            'label' => 'Privacy Policy',
            'url' => null,
            'page_id' => $privacyPage->id,
            'order' => 4,
            'target' => '_self',
        ]);

        // 5. Seed Sample Categories
        $categoriesData = [
            [
                'name' => 'Starters',
                'slug' => 'starters',
                'description' => 'Appetizers and freshly baked breads to start your meal.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Main Courses',
                'slug' => 'main-courses',
                'description' => 'Hearty handmade pastas and traditional chef specialties.',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Pizzas',
                'slug' => 'pizzas',
                'description' => 'Stone-hearth wood-fired pizzas with artisanal dough.',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Burgers',
                'slug' => 'burgers',
                'description' => 'Gourmet handcrafted burgers with prime cuts and brioche buns.',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Desserts',
                'slug' => 'desserts',
                'description' => 'Sweet handcrafted treats and Italian confections.',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Beverages',
                'slug' => 'beverages',
                'description' => 'Refreshing sodas, mineral waters, and authentic espresso.',
                'order' => 6,
                'is_active' => true,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $catData) {
            $categories[$catData['slug']] = Category::updateOrCreate(
                ['slug' => $catData['slug']],
                $catData
            );
        }

        // 6. Seed Sample Products & Variants
        $productsData = [
            // Starters
            [
                'category' => 'starters',
                'name' => 'Bruschetta al Pomodoro',
                'slug' => 'bruschetta-al-pomodoro',
                'description' => 'Toasted sourdough baguette topped with heirloom tomatoes, garlic, fresh basil, and extra virgin olive oil.',
                'base_price' => 8.50,
                'is_available' => true,
                'has_variants' => false,
                'variants' => [],
            ],
            [
                'category' => 'starters',
                'name' => 'Crispy Calamari Fritti',
                'slug' => 'crispy-calamari-fritti',
                'description' => 'Golden fried tender calamari rings served with house-made marinara sauce and fresh lemon wedges.',
                'base_price' => 12.00,
                'is_available' => true,
                'has_variants' => true,
                'variants' => [
                    ['name' => 'Regular Portion', 'type' => 'size', 'price_adjustment' => 0.00],
                    ['name' => 'Platter Portion', 'type' => 'size', 'price_adjustment' => 6.00],
                    ['name' => 'Extra Spicy Dip', 'type' => 'addon', 'price_adjustment' => 1.50],
                ],
            ],
            [
                'category' => 'starters',
                'name' => 'Garlic Mozzarella Bread',
                'slug' => 'garlic-mozzarella-bread',
                'description' => 'Artisan ciabatta brushed with garlic-herb butter and baked with melted whole milk mozzarella.',
                'base_price' => 6.50,
                'is_available' => true,
                'has_variants' => false,
                'variants' => [],
            ],

            // Pizzas
            [
                'category' => 'pizzas',
                'name' => 'Margherita Classica',
                'slug' => 'margherita-classica',
                'description' => 'San Marzano tomato sauce, fresh buffalo mozzarella, fresh basil leaves, and cold-pressed olive oil.',
                'base_price' => 14.00,
                'is_available' => true,
                'has_variants' => true,
                'variants' => [
                    ['name' => 'Small 10"', 'type' => 'size', 'price_adjustment' => 0.00],
                    ['name' => 'Medium 12"', 'type' => 'size', 'price_adjustment' => 3.50],
                    ['name' => 'Large 16"', 'type' => 'size', 'price_adjustment' => 7.00],
                    ['name' => 'Extra Buffalo Mozzarella', 'type' => 'addon', 'price_adjustment' => 2.50],
                    ['name' => 'Gluten-Free Crust', 'type' => 'option', 'price_adjustment' => 3.00],
                ],
            ],
            [
                'category' => 'pizzas',
                'name' => 'Pepperoni Rustica',
                'slug' => 'pepperoni-rustica',
                'description' => 'Cured artisanal pepperoni cups, aged mozzarella, oregano, and spicy hot honey drizzle.',
                'base_price' => 16.50,
                'is_available' => true,
                'has_variants' => true,
                'variants' => [
                    ['name' => 'Small 10"', 'type' => 'size', 'price_adjustment' => 0.00],
                    ['name' => 'Medium 12"', 'type' => 'size', 'price_adjustment' => 3.50],
                    ['name' => 'Large 16"', 'type' => 'size', 'price_adjustment' => 7.00],
                    ['name' => 'Extra Pepperoni', 'type' => 'addon', 'price_adjustment' => 2.50],
                ],
            ],
            [
                'category' => 'pizzas',
                'name' => 'Quattro Formaggi',
                'slug' => 'quattro-formaggi',
                'description' => 'Four cheese blend with mozzarella, gorgonzola dolce, fontina, and shaved parmigiano reggiano.',
                'base_price' => 17.50,
                'is_available' => true,
                'has_variants' => true,
                'variants' => [
                    ['name' => 'Medium 12"', 'type' => 'size', 'price_adjustment' => 0.00],
                    ['name' => 'Large 16"', 'type' => 'size', 'price_adjustment' => 4.50],
                ],
            ],

            // Main Courses
            [
                'category' => 'main-courses',
                'name' => 'Fettuccine Alfredo',
                'slug' => 'fettuccine-alfredo',
                'description' => 'Handmade fettuccine tossed in a rich, creamy Parmigiano-Reggiano and cultured butter sauce.',
                'base_price' => 15.50,
                'is_available' => true,
                'has_variants' => true,
                'variants' => [
                    ['name' => 'Standard', 'type' => 'size', 'price_adjustment' => 0.00],
                    ['name' => 'Add Grilled Chicken', 'type' => 'addon', 'price_adjustment' => 4.50],
                    ['name' => 'Add Sauteed Gulf Shrimp', 'type' => 'addon', 'price_adjustment' => 6.50],
                ],
            ],
            [
                'category' => 'main-courses',
                'name' => 'Spaghetti alla Carbonara',
                'slug' => 'spaghetti-alla-carbonara',
                'description' => 'Traditional Roman preparation with crispy guanciale, egg yolks, pecorino romano, and freshly cracked black pepper.',
                'base_price' => 16.50,
                'is_available' => true,
                'has_variants' => false,
                'variants' => [],
            ],
            [
                'category' => 'main-courses',
                'name' => 'Lasagna Bolognese al Forno',
                'slug' => 'lasagna-bolognese-al-forno',
                'description' => 'Slow-simmered beef and pork ragu layered with delicate egg pasta sheets, silky bechamel, and melted parmigiano.',
                'base_price' => 18.00,
                'is_available' => true,
                'has_variants' => false,
                'variants' => [],
            ],

            // Burgers
            [
                'category' => 'burgers',
                'name' => 'Truffle & Fontina Smash Burger',
                'slug' => 'truffle-fontina-smash-burger',
                'description' => 'Double prime Angus beef patties, melted Italian fontina, caramelized balsamic onions, and black truffle aioli on toasted brioche.',
                'base_price' => 15.00,
                'is_available' => true,
                'has_variants' => true,
                'variants' => [
                    ['name' => 'Single Patty', 'type' => 'size', 'price_adjustment' => -3.00],
                    ['name' => 'Double Patty', 'type' => 'size', 'price_adjustment' => 0.00],
                    ['name' => 'Triple Patty', 'type' => 'size', 'price_adjustment' => 3.50],
                    ['name' => 'Add Crispy Bacon', 'type' => 'addon', 'price_adjustment' => 2.00],
                    ['name' => 'Add Side of Fries', 'type' => 'addon', 'price_adjustment' => 3.50],
                ],
            ],

            // Desserts
            [
                'category' => 'desserts',
                'name' => 'Classic Tiramisu',
                'slug' => 'classic-tiramisu',
                'description' => 'Savoiardi ladyfingers soaked in espresso and Marsala wine, layered with mascarpone cream and dusted with Valrhona cocoa.',
                'base_price' => 8.00,
                'is_available' => true,
                'has_variants' => false,
                'variants' => [],
            ],
            [
                'category' => 'desserts',
                'name' => 'Sicilian Cannoli (2 pcs)',
                'slug' => 'sicilian-cannoli',
                'description' => 'Crisp pastry shells filled with sweet ricotta cream, dark chocolate chips, and candied orange peel.',
                'base_price' => 7.00,
                'is_available' => true,
                'has_variants' => false,
                'variants' => [],
            ],

            // Beverages
            [
                'category' => 'beverages',
                'name' => 'San Pellegrino Sparkling Water (750ml)',
                'slug' => 'san-pellegrino-750ml',
                'description' => 'Imported crisp Italian sparkling mineral water.',
                'base_price' => 4.50,
                'is_available' => true,
                'has_variants' => false,
                'variants' => [],
            ],
            [
                'category' => 'beverages',
                'name' => 'Italian Blood Orange Soda',
                'slug' => 'italian-blood-orange-soda',
                'description' => 'Sparkling citrus soda made with authentic Sicilian blood oranges.',
                'base_price' => 3.50,
                'is_available' => true,
                'has_variants' => false,
                'variants' => [],
            ],
        ];

        $seededProducts = [];
        foreach ($productsData as $pData) {
            $cat = $categories[$pData['category']] ?? null;
            if (! $cat) {
                continue;
            }

            $product = Product::updateOrCreate(
                ['slug' => $pData['slug']],
                [
                    'category_id' => $cat->id,
                    'name' => $pData['name'],
                    'description' => $pData['description'],
                    'base_price' => $pData['base_price'],
                    'is_available' => $pData['is_available'],
                    'has_variants' => $pData['has_variants'],
                ]
            );

            $seededProducts[] = $product;

            $product->variants()->delete();
            foreach ($pData['variants'] as $vData) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $vData['name'],
                    'type' => $vData['type'] ?? 'size',
                    'price_adjustment' => $vData['price_adjustment'] ?? 0.00,
                    'is_active' => true,
                ]);
            }
        }

        // 7. Seed Sample Orders
        if (! empty($seededProducts)) {
            $pizza = $seededProducts[3] ?? $seededProducts[0];
            $pasta = $seededProducts[6] ?? $seededProducts[0];

            // Order 1: New Pickup Order
            $order1 = Order::create([
                'user_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => '+1 (555) 345-6789',
                'order_type' => 'pickup',
                'delivery_address' => null,
                'order_notes' => 'Please include extra napkins.',
                'subtotal' => 32.00,
                'tax' => 2.72,
                'total' => 34.72,
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'order_status' => 'new',
            ]);

            OrderItem::create([
                'order_id' => $order1->id,
                'product_id' => $pizza->id,
                'product_name' => $pizza->name,
                'quantity' => 1,
                'unit_price' => 17.50,
                'variants_selected' => [['name' => 'Medium 12"', 'type' => 'size', 'price_adjustment' => 3.50]],
                'total_price' => 17.50,
            ]);

            OrderItem::create([
                'order_id' => $order1->id,
                'product_id' => $pasta->id,
                'product_name' => $pasta->name,
                'quantity' => 1,
                'unit_price' => 14.50,
                'variants_selected' => null,
                'total_price' => 14.50,
            ]);

            // Order 2: Preparing Delivery Order
            $order2 = Order::create([
                'user_id' => null,
                'customer_name' => 'Emily Watson',
                'customer_email' => 'emily.watson@example.com',
                'customer_phone' => '+1 (555) 987-6543',
                'order_type' => 'delivery',
                'delivery_address' => '789 Broadway Ave, Apt 4B, New York, NY 10003',
                'order_notes' => 'Ring apartment 4B buzzer.',
                'subtotal' => 25.00,
                'tax' => 2.13,
                'total' => 32.12, // subtotal + tax + delivery fee (4.99)
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'order_status' => 'preparing',
            ]);

            OrderItem::create([
                'order_id' => $order2->id,
                'product_id' => $pasta->id,
                'product_name' => $pasta->name,
                'quantity' => 1,
                'unit_price' => 20.00,
                'variants_selected' => [['name' => 'Add Grilled Chicken', 'type' => 'addon', 'price_adjustment' => 4.50]],
                'total_price' => 20.00,
            ]);

            // Order 3: Completed Order
            $order3 = Order::create([
                'user_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => '+1 (555) 345-6789',
                'order_type' => 'pickup',
                'delivery_address' => null,
                'order_notes' => null,
                'subtotal' => 14.00,
                'tax' => 1.19,
                'total' => 15.19,
                'payment_method' => 'cod',
                'payment_status' => 'paid',
                'order_status' => 'completed',
            ]);

            OrderItem::create([
                'order_id' => $order3->id,
                'product_id' => $pizza->id,
                'product_name' => $pizza->name,
                'quantity' => 1,
                'unit_price' => 14.00,
                'variants_selected' => null,
                'total_price' => 14.00,
            ]);
        }
    }
}
