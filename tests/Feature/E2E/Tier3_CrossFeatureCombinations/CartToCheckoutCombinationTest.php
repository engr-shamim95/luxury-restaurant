<?php

namespace Tests\Feature\E2E\Tier3_CrossFeatureCombinations;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartToCheckoutCombinationTest extends TestCase
{
    use RefreshDatabase;

    protected Product $simpleProduct;
    protected Product $variantProduct;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::create(['key' => 'tax_rate', 'value' => '10', 'type' => 'string']);
        Setting::create(['key' => 'delivery_fee', 'value' => '5.00', 'type' => 'string']);

        $category = Category::create(['name' => 'Pizza & Sides', 'slug' => 'pizza-sides', 'is_active' => true]);

        $this->simpleProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Garlic Herb Focaccia',
            'slug' => 'garlic-focaccia',
            'base_price' => 6.00,
            'is_available' => true,
        ]);

        $this->variantProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Prosciutto e Rucola',
            'slug' => 'prosciutto-rucola',
            'base_price' => 16.00,
            'is_available' => true,
            'has_variants' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $this->variantProduct->id,
            'name' => 'Large 16"',
            'type' => 'size',
            'price_adjustment' => 4.00,
            'is_active' => true,
        ]);
    }

    public function test_cart_accumulates_mixed_items_and_flows_to_checkout(): void
    {
        // 1. Add simple item
        $this->post('/cart/add', [
            'product_id' => $this->simpleProduct->id,
            'quantity' => 2,
        ])->assertRedirect();

        // 2. Add variant item
        $this->post('/cart/add', [
            'product_id' => $this->variantProduct->id,
            'variant_id' => $this->variant->id,
            'quantity' => 1,
        ])->assertRedirect();

        // Check cart session
        $cart = session('cart');
        $this->assertCount(2, $cart);

        // 3. View cart page
        $cartView = $this->get('/cart');
        $cartView->assertStatus(200);
        $cartView->assertSee('Garlic Herb Focaccia');
        $cartView->assertSee('Prosciutto e Rucola');

        // 4. View checkout page
        $checkoutView = $this->get('/checkout');
        $checkoutView->assertStatus(200);
        $checkoutView->assertSee('Garlic Herb Focaccia');
        $checkoutView->assertSee('Prosciutto e Rucola');
    }

    public function test_modifying_cart_quantities_updates_checkout_totals(): void
    {
        $simpleKey = "item_{$this->simpleProduct->id}_simple";
        $initialCart = [
            $simpleKey => [
                'item_key' => $simpleKey,
                'product_id' => $this->simpleProduct->id,
                'product_name' => $this->simpleProduct->name,
                'price' => 6.00,
                'quantity' => 1,
            ],
        ];

        // Customer updates quantity from 1 to 5
        $this->withSession(['cart' => $initialCart])
            ->patch('/cart/update', [
                'item_key' => $simpleKey,
                'quantity' => 5,
            ])
            ->assertRedirect();

        $cart = session('cart');
        $this->assertEquals(5, $cart[$simpleKey]['quantity']);

        // Subtotal = 5 * 6.00 = 30.00
        $checkoutView = $this->get('/checkout');
        $checkoutView->assertStatus(200);
        $checkoutView->assertSee('30.00');
    }
}
