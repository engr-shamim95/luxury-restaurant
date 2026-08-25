<?php

namespace Tests\Feature\E2E\Tier1_FeatureCoverage;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;
    protected array $sampleCart;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::create(['key' => 'tax_rate', 'value' => '10', 'type' => 'string']);
        Setting::create(['key' => 'delivery_fee', 'value' => '5.00', 'type' => 'string']);

        $category = Category::create(['name' => 'Meals', 'slug' => 'meals', 'is_active' => true]);
        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Calzone Napoletano',
            'slug' => 'calzone-napoletano',
            'base_price' => 15.00,
            'is_available' => true,
        ]);

        $this->sampleCart = [
            'item_1_simple' => [
                'item_key' => 'item_1_simple',
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'variant_id' => null,
                'variant_name' => null,
                'price' => 15.00,
                'quantity' => 2,
            ],
        ];
    }

    public function test_checkout_page_renders_when_cart_is_not_empty(): void
    {
        $response = $this->withSession(['cart' => $this->sampleCart])
            ->get('/checkout');

        $response->assertStatus(200);
        $response->assertSee('Calzone Napoletano');
    }

    public function test_customer_can_submit_pickup_order(): void
    {
        $payload = [
            'customer_name' => 'Mario Rossi',
            'customer_email' => 'mario@rossi.it',
            'customer_phone' => '555-4321',
            'order_type' => 'pickup',
            'order_notes' => 'Extra napkins please',
            'payment_method' => 'cash',
        ];

        $response = $this->withSession(['cart' => $this->sampleCart])
            ->post('/checkout', $payload);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Mario Rossi',
            'customer_email' => 'mario@rossi.it',
            'order_type' => 'pickup',
            'payment_method' => 'cash',
            'subtotal' => 30.00,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product->id,
            'product_name' => 'Calzone Napoletano',
            'quantity' => 2,
            'unit_price' => 15.00,
            'total_price' => 30.00,
        ]);
    }

    public function test_customer_can_submit_delivery_order_with_address(): void
    {
        $payload = [
            'customer_name' => 'Luigi Verdi',
            'customer_email' => 'luigi@verdi.it',
            'customer_phone' => '555-8765',
            'order_type' => 'delivery',
            'delivery_address' => 'Via Roma 42, Apt 3B, New York',
            'order_notes' => 'Ring bell #3B',
            'payment_method' => 'card',
        ];

        $response = $this->withSession(['cart' => $this->sampleCart])
            ->post('/checkout', $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Luigi Verdi',
            'order_type' => 'delivery',
            'delivery_address' => 'Via Roma 42, Apt 3B, New York',
            'payment_method' => 'card',
        ]);
    }

    public function test_checkout_submission_clears_session_cart(): void
    {
        $payload = [
            'customer_name' => 'Antonio Vivaldi',
            'customer_email' => 'vivaldi@venice.it',
            'customer_phone' => '555-1741',
            'order_type' => 'pickup',
            'payment_method' => 'cash',
        ];

        $response = $this->withSession(['cart' => $this->sampleCart])
            ->post('/checkout', $payload);

        $response->assertRedirect();
        $this->assertEmpty(session('cart'));
    }

    public function test_order_confirmation_page_displays_order_details_and_items(): void
    {
        $order = Order::create([
            'customer_name' => 'Giacomo Puccini',
            'customer_email' => 'puccini@opera.it',
            'customer_phone' => '555-1924',
            'order_type' => 'pickup',
            'subtotal' => 30.00,
            'tax' => 3.00,
            'total' => 33.00,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'order_status' => 'new',
        ]);

        $response = $this->get("/order/confirmation/{$order->id}");

        $response->assertStatus(200);
        $response->assertSee('Giacomo Puccini');
        $response->assertSee('33.00');
    }
}
