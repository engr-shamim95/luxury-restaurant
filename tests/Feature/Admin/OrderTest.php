<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_orders_list(): void
    {
        $admin = User::factory()->admin()->create();
        Order::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.orders.index'));

        $response->assertStatus(200);
        $response->assertSee('Customer Orders Management');
    }

    public function test_admin_can_view_single_order_details(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create([
            'customer_name' => 'Marco Rossi',
            'order_type' => 'delivery',
            'delivery_address' => '12 Via Roma',
        ]);
        $product = Product::factory()->create(['name' => 'Lasagna']);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Lasagna',
            'quantity' => 2,
            'unit_price' => 15.00,
            'total_price' => 30.00,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order));

        $response->assertStatus(200);
        $response->assertSee('Marco Rossi');
        $response->assertSee('Lasagna');
        $response->assertSee('12 Via Roma');
    }

    public function test_admin_can_update_order_status(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create([
            'order_status' => Order::STATUS_NEW,
            'payment_status' => Order::PAYMENT_PENDING,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.orders.update-status', $order), [
            'order_status' => Order::STATUS_READY,
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => Order::STATUS_READY,
            'payment_status' => Order::PAYMENT_PAID,
        ]);
    }

    public function test_admin_can_view_printable_receipt(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create(['customer_name' => 'Luigi Mario']);

        $response = $this->actingAs($admin)->get(route('admin.orders.print', $order));

        $response->assertStatus(200);
        $response->assertSee('Luigi Mario');
        $response->assertSee('ORDER #' . $order->id);
    }

    public function test_admin_can_delete_order(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.orders.destroy', $order));

        $response->assertRedirect(route('admin.orders.index'));
        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
        ]);
    }
}
