<?php

namespace Tests\Feature\E2E\Tier2_BoundaryAndCornerCases;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndCheckoutBoundaryTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['name' => 'Meals', 'slug' => 'meals', 'is_active' => true]);
        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Artisan Calzone',
            'slug' => 'artisan-calzone',
            'base_price' => 12.50,
            'is_available' => true,
        ]);
    }

    public function test_checkout_access_with_empty_cart_redirects_with_notice(): void
    {
        $response = $this->withSession(['cart' => []])->get('/checkout');

        $response->assertRedirect();
    }

    public function test_checkout_post_with_empty_cart_is_rejected(): void
    {
        $response = $this->withSession(['cart' => []])->post('/checkout', [
            'customer_name' => 'Ghost Customer',
            'customer_email' => 'ghost@test.com',
            'customer_phone' => '555-0000',
            'order_type' => 'pickup',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('orders', ['customer_email' => 'ghost@test.com']);
    }

    public function test_adding_unavailable_product_to_cart_fails(): void
    {
        $unavailableProduct = Product::create([
            'category_id' => $this->product->category_id,
            'name' => 'Sold Out Soup',
            'slug' => 'sold-out-soup',
            'base_price' => 8.00,
            'is_available' => false,
        ]);

        $response = $this->post('/cart/add', [
            'product_id' => $unavailableProduct->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $cart = session('cart', []);
        $this->assertEmpty($cart);
    }

    public function test_adding_negative_or_zero_quantity_to_cart_fails_validation(): void
    {
        $response = $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 0,
        ]);

        $response->assertSessionHasErrors(['quantity']);
    }

    public function test_delivery_order_fails_when_delivery_address_is_missing(): void
    {
        $cart = [
            'item_1' => [
                'item_key' => 'item_1',
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'price' => 12.50,
                'quantity' => 1,
            ],
        ];

        $response = $this->withSession(['cart' => $cart])->post('/checkout', [
            'customer_name' => 'John Delivery',
            'customer_email' => 'delivery@test.com',
            'customer_phone' => '555-9876',
            'order_type' => 'delivery',
            'delivery_address' => '', // Missing address
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors(['delivery_address']);
        $this->assertDatabaseMissing('orders', ['customer_email' => 'delivery@test.com']);
    }

    public function test_checkout_fails_with_invalid_email_format(): void
    {
        $cart = [
            'item_1' => [
                'item_key' => 'item_1',
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'price' => 12.50,
                'quantity' => 1,
            ],
        ];

        $response = $this->withSession(['cart' => $cart])->post('/checkout', [
            'customer_name' => 'Bad Email User',
            'customer_email' => 'not-an-email-address',
            'customer_phone' => '555-9876',
            'order_type' => 'pickup',
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors(['customer_email']);
    }
}
