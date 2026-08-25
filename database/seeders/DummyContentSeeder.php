<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DummyContentSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Dummy Pages
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '<div class="prose max-w-none">
                    <h2>Our Story</h2>
                    <p>Welcome to <strong>Bella Vista Ristorante</strong>. Founded in 1998, we have been serving the finest, authentic Italian cuisine to our beloved community for over two decades. Our recipes have been passed down through generations, ensuring every bite is a taste of tradition.</p>
                    <p>We source only the freshest local ingredients and import the finest cheeses and olive oils directly from Italy. Whether you are craving a classic Margherita pizza fresh from our wood-fired oven or a rich, creamy Fettuccine Alfredo, we promise an unforgettable dining experience.</p>
                    <p>Come for the food, stay for the family atmosphere. <em>Buon Appetito!</em></p>
                </div>',
                'is_published' => true,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact',
                'content' => '<div class="prose max-w-none">
                    <h2>Get In Touch</h2>
                    <p>We would love to hear from you! Whether you want to make a large reservation, inquire about catering, or just leave some feedback.</p>
                    <ul>
                        <li><strong>Address:</strong> 123 Culinary Boulevard, Foodville, FL 33012</li>
                        <li><strong>Phone:</strong> (555) 123-4567</li>
                        <li><strong>Email:</strong> info@bellavista.test</li>
                    </ul>
                    <p>Our customer service team is available Monday to Sunday from 10:00 AM to 10:00 PM.</p>
                </div>',
                'is_published' => true,
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms-conditions',
                'content' => '<div class="prose max-w-none">
                    <h2>Terms and Conditions</h2>
                    <p>Welcome to our website. If you continue to browse and use this website, you are agreeing to comply with and be bound by the following terms and conditions of use.</p>
                    <p><strong>1. Delivery Policy:</strong> We aim to deliver within 45 minutes of order confirmation. Delivery times may vary depending on traffic and weather conditions.</p>
                    <p><strong>2. Refund Policy:</strong> If you are not satisfied with your order, please contact us within 2 hours of delivery for a replacement or refund.</p>
                    <p><strong>3. Payment:</strong> We accept all major credit cards. Payments are processed securely via Stripe/Square.</p>
                </div>',
                'is_published' => true,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<div class="prose max-w-none">
                    <h2>Privacy Policy</h2>
                    <p>This privacy policy sets out how Bella Vista Ristorante uses and protects any information that you give when you use this website.</p>
                    <p>We are committed to ensuring that your privacy is protected. Should we ask you to provide certain information by which you can be identified when using this website, then you can be assured that it will only be used in accordance with this privacy statement.</p>
                </div>',
                'is_published' => true,
            ]
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }

        // 2. Create Dummy Customers
        $customers = [];
        for ($i = 1; $i <= 5; $i++) {
            $customers[] = User::firstOrCreate(
                ['email' => "customer{$i}@example.com"],
                [
                    'name' => "Customer {$i}",
                    'password' => Hash::make('password'),
                    'is_admin' => false,
                ]
            );
        }

        // 3. Create Dummy Orders
        $products = Product::all();
        if ($products->count() > 0) {
            $statuses = ['pending', 'processing', 'completed', 'cancelled'];
            
            for ($i = 0; $i < 15; $i++) {
                $customer = $customers[array_rand($customers)];
                $status = $statuses[array_rand($statuses)];
                
                // Pick 1-3 random products
                $orderProducts = $products->random(rand(1, 3));
                $subtotal = 0;
                
                $order = Order::create([
                    'user_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'customer_phone' => '555-' . rand(1000, 9999),
                    'order_type' => array_rand(array_flip(['pickup', 'delivery'])),
                    'delivery_address' => rand(100, 999) . ' Main St, Apt ' . rand(1, 20),
                    'subtotal' => 0, // calculated below
                    'tax' => 0,
                    'total' => 0,
                    'order_status' => $status,
                    'payment_method' => array_rand(array_flip(['credit_card', 'cash_on_delivery'])),
                    'payment_status' => $status === 'completed' ? 'paid' : 'pending',
                    'order_notes' => rand(0, 1) ? 'Please leave at the door' : null,
                ]);
                
                foreach ($orderProducts as $product) {
                    $qty = rand(1, 3);
                    $price = $product->base_price;
                    $subtotal += ($price * $qty);
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total_price' => $price * $qty,
                    ]);
                }
                
                $tax = $subtotal * 0.08; // 8% tax
                $total = $subtotal + $tax;
                
                $order->update([
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                ]);
            }
        }
        
        echo "Dummy Pages, Customers, and Orders seeded successfully!\n";
    }
}
