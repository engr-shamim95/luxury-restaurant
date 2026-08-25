<?php

namespace Tests\Feature\E2E\Tier4_RealWorldScenarios;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderJourneyScenarioTest extends TestCase
{
    use RefreshDatabase;

    protected Category $pizzaCategory;
    protected Category $drinksCategory;
    protected Product $pizzaProduct;
    protected Product $drinkProduct;
    protected ProductVariant $sizeVariant;
    protected ProductVariant $addonVariant;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::create(['key' => 'restaurant_name', 'value' => 'La Bella Vita Ristorante', 'type' => 'string']);
        Setting::create(['key' => 'tax_rate', 'value' => '8.5', 'type' => 'string']);
        Setting::create(['key' => 'delivery_fee', 'value' => '4.50', 'type' => 'string']);

        $this->pizzaCategory = Category::create([
            'name' => 'Wood-Fired Pizzas',
            'slug' => 'wood-fired-pizzas',
            'is_active' => true,
            'order' => 1,
        ]);

        $this->drinksCategory = Category::create([
            'name' => 'Cold Beverages',
            'slug' => 'cold-beverages',
            'is_active' => true,
            'order' => 2,
        ]);

        $this->pizzaProduct = Product::create([
            'category_id' => $this->pizzaCategory->id,
            'name' => 'Quattro Formaggi Speciale',
            'slug' => 'quattro-formaggi-speciale',
            'description' => 'Gorgonzola, mozzarella, parmesan, fontina, white truffle cream.',
            'base_price' => 16.00,
            'is_available' => true,
            'has_variants' => true,
        ]);

        $this->sizeVariant = ProductVariant::create([
            'product_id' => $this->pizzaProduct->id,
            'name' => 'Large 16"',
            'type' => 'size',
            'price_adjustment' => 4.50,
            'is_active' => true,
        ]);

        $this->drinkProduct = Product::create([
            'category_id' => $this->drinksCategory->id,
            'name' => 'San Pellegrino Limonata',
            'slug' => 'san-pellegrino-limonata',
            'base_price' => 3.50,
            'is_available' => true,
            'has_variants' => false,
        ]);
    }

    public function test_complete_end_to_end_customer_ordering_journey(): void
    {
        // 1. Customer visits landing page
        $home = $this->get('/');
        $home->assertStatus(200);
        $home->assertSee('La Bella Vita Ristorante');

        // 2. Customer navigates to menu
        $menu = $this->get('/menu');
        $menu->assertStatus(200);
        $menu->assertSee('Quattro Formaggi Speciale');
        $menu->assertSee('San Pellegrino Limonata');

        // 3. Customer adds Pizza with Large variant to cart
        $this->post('/cart/add', [
            'product_id' => $this->pizzaProduct->id,
            'variant_id' => $this->sizeVariant->id,
            'quantity' => 1,
        ])->assertRedirect();

        // 4. Customer adds Beverage to cart
        $this->post('/cart/add', [
            'product_id' => $this->drinkProduct->id,
            'quantity' => 1,
        ])->assertRedirect();

        // Verify cart contains 2 distinct line items
        $cart = session('cart');
        $this->assertCount(2, $cart);

        // 5. Customer inspects cart page
        $cartPage = $this->get('/cart');
        $cartPage->assertStatus(200);
        $cartPage->assertSee('Quattro Formaggi Speciale');
        $cartPage->assertSee('San Pellegrino Limonata');

        // 6. Customer updates beverage quantity to 2
        $drinkKey = "item_{$this->drinkProduct->id}_simple";
        $this->patch('/cart/update', [
            'item_key' => $drinkKey,
            'quantity' => 2,
        ])->assertRedirect();

        // 7. Customer navigates to checkout
        $checkout = $this->get('/checkout');
        $checkout->assertStatus(200);

        // 8. Customer submits delivery order
        $checkoutResponse = $this->post('/checkout', [
            'customer_name' => 'Sophia Loren',
            'customer_email' => 'sophia@cinema.it',
            'customer_phone' => '+1 (555) 777-8899',
            'order_type' => 'delivery',
            'delivery_address' => '77 Sunset Boulevard, Suite 500, Los Angeles',
            'order_notes' => 'Gate code #4421. Please do not ring doorbell after 8pm.',
            'payment_method' => 'cash',
        ]);

        $checkoutResponse->assertRedirect();

        // 9. Verify Database Records
        $order = Order::where('customer_email', 'sophia@cinema.it')->firstOrFail();
        $this->assertEquals('Sophia Loren', $order->customer_name);
        $this->assertEquals('delivery', $order->order_type);
        $this->assertEquals('77 Sunset Boulevard, Suite 500, Los Angeles', $order->delivery_address);
        $this->assertEquals('Gate code #4421. Please do not ring doorbell after 8pm.', $order->order_notes);

        // Expected subtotal: Pizza (16.00 + 4.50 = 20.50) + 2x Drink (2 * 3.50 = 7.00) = 27.50
        $this->assertEquals(27.50, $order->subtotal);

        // Verify Order Items
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->pizzaProduct->id,
            'quantity' => 1,
            'unit_price' => 20.50,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->drinkProduct->id,
            'quantity' => 2,
            'unit_price' => 3.50,
        ]);

        // Verify Cart is emptied
        $this->assertEmpty(session('cart'));

        // 10. Customer views confirmation receipt
        $confirmation = $this->get("/order/confirmation/{$order->id}");
        $confirmation->assertStatus(200);
        $confirmation->assertSee('Sophia Loren');
        $confirmation->assertSee('Quattro Formaggi Speciale');
        $confirmation->assertSee('San Pellegrino Limonata');
    }
}
