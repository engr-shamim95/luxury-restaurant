<?php

namespace Tests\Feature\E2E\Tier1_FeatureCoverage;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendCartTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create([
            'name' => 'Main Menu',
            'slug' => 'main-menu',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Pepperoni Passion',
            'slug' => 'pepperoni-passion',
            'base_price' => 14.00,
            'is_available' => true,
            'has_variants' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'name' => 'Large 16"',
            'type' => 'size',
            'price_adjustment' => 4.00,
            'is_active' => true,
        ]);
    }

    public function test_cart_page_renders_successfully(): void
    {
        $response = $this->get('/cart');

        $response->assertStatus(200);
    }

    public function test_customer_can_add_simple_product_to_cart(): void
    {
        $response = $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('cart');
        
        $cart = session('cart');
        $this->assertNotEmpty($cart);
        
        $firstItem = reset($cart);
        $this->assertEquals($this->product->id, $firstItem['product_id']);
        $this->assertEquals(2, $firstItem['quantity']);
        $this->assertEquals(14.00, $firstItem['price']);
    }

    public function test_customer_can_add_product_with_variant_to_cart(): void
    {
        $response = $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('cart');

        $cart = session('cart');
        $item = reset($cart);

        $this->assertEquals($this->product->id, $item['product_id']);
        $this->assertEquals($this->variant->id, $item['variant_id']);
        $this->assertEquals(18.00, $item['price']); // 14.00 base + 4.00 variant
    }

    public function test_customer_can_update_cart_item_quantity(): void
    {
        $itemKey = "item_{$this->product->id}_simple";
        $initialCart = [
            $itemKey => [
                'item_key' => $itemKey,
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'variant_id' => null,
                'variant_name' => null,
                'price' => 14.00,
                'quantity' => 1,
            ],
        ];

        $response = $this->withSession(['cart' => $initialCart])
            ->patch('/cart/update', [
                'item_key' => $itemKey,
                'quantity' => 4,
            ]);

        $response->assertRedirect();
        $cart = session('cart');
        $this->assertEquals(4, $cart[$itemKey]['quantity']);
    }

    public function test_customer_can_remove_item_from_cart(): void
    {
        $itemKey = "item_{$this->product->id}_simple";
        $initialCart = [
            $itemKey => [
                'item_key' => $itemKey,
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'price' => 14.00,
                'quantity' => 1,
            ],
        ];

        $response = $this->withSession(['cart' => $initialCart])
            ->delete("/cart/remove/{$itemKey}");

        $response->assertRedirect();
        $cart = session('cart');
        $this->assertArrayNotHasKey($itemKey, $cart ?? []);
    }

    public function test_customer_can_clear_entire_cart(): void
    {
        $initialCart = [
            'item_1' => ['product_id' => 1, 'quantity' => 2, 'price' => 10.00],
            'item_2' => ['product_id' => 2, 'quantity' => 1, 'price' => 15.00],
        ];

        $response = $this->withSession(['cart' => $initialCart])
            ->post('/cart/clear');

        $response->assertRedirect();
        $cart = session('cart');
        $this->assertEmpty($cart);
    }
}
