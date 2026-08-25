<?php

namespace Tests\Feature\E2E\Tier1_FeatureCoverage;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminOrdersTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin Manager',
            'email' => 'admin@restaurant.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_view_orders_index(): void
    {
        Order::create([
            'customer_name' => 'Michael Corleone',
            'customer_email' => 'michael@sicily.com',
            'customer_phone' => '555-900-1122',
            'order_type' => 'delivery',
            'delivery_address' => 'Mall Compound, Long Beach, NY',
            'subtotal' => 45.00,
            'tax' => 4.50,
            'total' => 49.50,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'order_status' => 'new',
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertSee('Michael Corleone');
        $response->assertSee('49.50');
    }

    public function test_admin_can_view_order_details_with_items_and_variants(): void
    {
        $order = Order::create([
            'customer_name' => 'Vito Genovese',
            'customer_email' => 'vito@family.com',
            'customer_phone' => '555-123-4567',
            'order_type' => 'pickup',
            'subtotal' => 28.00,
            'tax' => 2.80,
            'total' => 30.80,
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'order_status' => 'new',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_name' => 'Special Calzone',
            'quantity' => 2,
            'unit_price' => 14.00,
            'variants_selected' => ['name' => 'Extra Ricotta (+$2.00)'],
            'total_price' => 28.00,
        ]);

        $response = $this->actingAs($this->adminUser)->get("/admin/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertSee('Vito Genovese');
        $response->assertSee('Special Calzone');
        $response->assertSee('Extra Ricotta');
    }

    public function test_admin_can_update_order_status_to_preparing(): void
    {
        $order = Order::create([
            'customer_name' => 'Frank Sinatra',
            'customer_email' => 'frank@hoboken.com',
            'subtotal' => 20.00,
            'tax' => 2.00,
            'total' => 22.00,
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'order_status' => 'new',
        ]);

        $response = $this->actingAs($this->adminUser)->patch("/admin/orders/{$order->id}/status", [
            'order_status' => 'preparing',
            'payment_status' => 'paid',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'preparing',
        ]);
    }

    public function test_admin_can_update_order_status_to_ready_and_completed(): void
    {
        $order = Order::create([
            'customer_name' => 'Dean Martin',
            'customer_email' => 'dean@vegas.com',
            'subtotal' => 35.00,
            'tax' => 3.50,
            'total' => 38.50,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'order_status' => 'preparing',
        ]);

        $this->actingAs($this->adminUser)->patch("/admin/orders/{$order->id}/status", [
            'order_status' => 'ready',
            'payment_status' => 'pending',
        ]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_status' => 'ready']);

        $this->actingAs($this->adminUser)->patch("/admin/orders/{$order->id}/status", [
            'order_status' => 'completed',
            'payment_status' => 'paid',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'completed',
            'payment_status' => 'paid',
        ]);
    }

    public function test_admin_can_view_printable_order_receipt(): void
    {
        $order = Order::create([
            'customer_name' => 'Luciano Pavarotti',
            'customer_email' => 'luciano@opera.it',
            'subtotal' => 50.00,
            'tax' => 5.00,
            'total' => 55.00,
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);

        $response = $this->actingAs($this->adminUser)->get("/admin/orders/{$order->id}/print");

        $response->assertStatus(200);
        $response->assertSee('Luciano Pavarotti');
        $response->assertSee('55.00');
    }

    public function test_admin_can_delete_order(): void
    {
        $order = Order::create([
            'customer_name' => 'Test Dummy',
            'customer_email' => 'dummy@test.com',
            'subtotal' => 10.00,
            'tax' => 1.00,
            'total' => 11.00,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'order_status' => 'cancelled',
        ]);

        $response = $this->actingAs($this->adminUser)->delete("/admin/orders/{$order->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }
}
