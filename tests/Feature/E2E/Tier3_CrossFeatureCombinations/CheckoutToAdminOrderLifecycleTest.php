<?php

namespace Tests\Feature\E2E\Tier3_CrossFeatureCombinations;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CheckoutToAdminOrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Product $product;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Manager',
            'email' => 'manager@restaurant.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        $category = Category::create(['name' => 'Main Dishes', 'slug' => 'main-dishes', 'is_active' => true]);
        
        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Pollo alla Cacciatora',
            'slug' => 'pollo-cacciatora',
            'base_price' => 18.00,
            'is_available' => true,
            'has_variants' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'name' => 'Add Polenta Side',
            'type' => 'addon',
            'price_adjustment' => 4.00,
            'is_active' => true,
        ]);
    }

    public function test_complete_order_lifecycle_from_customer_checkout_to_admin_fulfillment(): void
    {
        // 1. Customer adds item to cart
        $cart = [
            'item_custom' => [
                'item_key' => 'item_custom',
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'variant_id' => $this->variant->id,
                'variant_name' => 'Add Polenta Side (+ $4.00)',
                'price' => 22.00,
                'quantity' => 2,
            ],
        ];

        // 2. Customer completes checkout
        $checkoutResponse = $this->withSession(['cart' => $cart])->post('/checkout', [
            'customer_name' => 'Leonardo da Vinci',
            'customer_email' => 'leonardo@florence.it',
            'customer_phone' => '555-1519',
            'order_type' => 'delivery',
            'delivery_address' => 'Piazza della Signoria 1, Florence',
            'order_notes' => 'Ring doorbell twice please',
            'payment_method' => 'cash',
        ]);

        $checkoutResponse->assertRedirect();
        $this->assertEmpty(session('cart'));

        // Verify order in database
        $order = Order::where('customer_email', 'leonardo@florence.it')->firstOrFail();
        $this->assertEquals('Leonardo da Vinci', $order->customer_name);
        $this->assertEquals('new', $order->order_status);
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals(44.00, $order->subtotal);

        // Verify order items in database
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 22.00,
            'total_price' => 44.00,
        ]);

        // 3. Admin logs in and views order list
        $adminOrdersList = $this->actingAs($this->admin)->get('/admin/orders');
        $adminOrdersList->assertStatus(200);
        $adminOrdersList->assertSee('Leonardo da Vinci');

        // 4. Admin views order detail
        $adminOrderDetail = $this->actingAs($this->admin)->get("/admin/orders/{$order->id}");
        $adminOrderDetail->assertStatus(200);
        $adminOrderDetail->assertSee('Piazza della Signoria 1, Florence');
        $adminOrderDetail->assertSee('Ring doorbell twice please');
        $adminOrderDetail->assertSee('Add Polenta Side');

        // 5. Admin updates status to preparing
        $this->actingAs($this->admin)->patch("/admin/orders/{$order->id}/status", [
            'order_status' => 'preparing',
            'payment_status' => 'pending',
        ])->assertRedirect();
        $this->assertEquals('preparing', $order->fresh()->order_status);

        // 6. Admin updates status to completed and paid
        $this->actingAs($this->admin)->patch("/admin/orders/{$order->id}/status", [
            'order_status' => 'completed',
            'payment_status' => 'paid',
        ])->assertRedirect();
        
        $freshOrder = $order->fresh();
        $this->assertEquals('completed', $freshOrder->order_status);
        $this->assertEquals('paid', $freshOrder->payment_status);
    }
}
