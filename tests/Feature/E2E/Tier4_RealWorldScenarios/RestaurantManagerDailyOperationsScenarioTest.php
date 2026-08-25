<?php

namespace Tests\Feature\E2E\Tier4_RealWorldScenarios;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RestaurantManagerDailyOperationsScenarioTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::create([
            'name' => 'General Manager Marco',
            'email' => 'marco@bellanapoli.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);
    }

    public function test_restaurant_manager_daily_order_processing_and_fulfillment_workflow(): void
    {
        // 1. Manager logs in and views admin dashboard
        $dashboard = $this->actingAs($this->manager)->get('/admin');
        $dashboard->assertStatus(200);

        // 2. Incoming customer orders populate database
        $order1 = Order::create([
            'customer_name' => 'Order One Customer',
            'customer_email' => 'order1@test.com',
            'customer_phone' => '555-1111',
            'order_type' => 'delivery',
            'delivery_address' => '101 Pine St',
            'subtotal' => 35.00,
            'tax' => 3.50,
            'total' => 38.50,
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'order_status' => 'new',
        ]);
        OrderItem::create([
            'order_id' => $order1->id,
            'product_name' => 'Pizza Margherita',
            'quantity' => 2,
            'unit_price' => 17.50,
            'total_price' => 35.00,
        ]);

        $order2 = Order::create([
            'customer_name' => 'Order Two Customer',
            'customer_email' => 'order2@test.com',
            'customer_phone' => '555-2222',
            'order_type' => 'pickup',
            'subtotal' => 20.00,
            'tax' => 2.00,
            'total' => 22.00,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'order_status' => 'new',
        ]);

        // 3. Manager visits orders management screen
        $ordersIndex = $this->actingAs($this->manager)->get('/admin/orders');
        $ordersIndex->assertStatus(200);
        $ordersIndex->assertSee('Order One Customer');
        $ordersIndex->assertSee('Order Two Customer');

        // 4. Manager opens Order 1 and transitions to "preparing"
        $this->actingAs($this->manager)->patch("/admin/orders/{$order1->id}/status", [
            'order_status' => 'preparing',
            'payment_status' => 'paid',
        ])->assertRedirect();
        $this->assertEquals('preparing', $order1->fresh()->order_status);

        // 5. Kitchen completes Order 1 -> manager marks as "ready"
        $this->actingAs($this->manager)->patch("/admin/orders/{$order1->id}/status", [
            'order_status' => 'ready',
            'payment_status' => 'paid',
        ])->assertRedirect();
        $this->assertEquals('ready', $order1->fresh()->order_status);

        // 6. Manager views printable kitchen receipt
        $receipt = $this->actingAs($this->manager)->get("/admin/orders/{$order1->id}/print");
        $receipt->assertStatus(200);
        $receipt->assertSee('Pizza Margherita');
        $receipt->assertSee('38.50');

        // 7. Driver delivers Order 1 -> manager marks as "completed"
        $this->actingAs($this->manager)->patch("/admin/orders/{$order1->id}/status", [
            'order_status' => 'completed',
            'payment_status' => 'paid',
        ])->assertRedirect();
        $this->assertEquals('completed', $order1->fresh()->order_status);

        // 8. Order 2 customer cancels pickup -> manager updates status to "cancelled"
        $this->actingAs($this->manager)->patch("/admin/orders/{$order2->id}/status", [
            'order_status' => 'cancelled',
            'payment_status' => 'pending',
        ])->assertRedirect();
        $this->assertEquals('cancelled', $order2->fresh()->order_status);
    }
}
