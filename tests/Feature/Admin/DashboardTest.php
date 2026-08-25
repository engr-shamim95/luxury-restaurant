<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_is_redirected_away(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertRedirect('/');
    }

    public function test_admin_can_view_dashboard_with_metrics(): void
    {
        $admin = User::factory()->admin()->create();

        Order::factory()->count(3)->create([
            'order_status' => 'new',
            'subtotal' => 20.00,
            'tax' => 2.00,
            'total' => 22.00,
        ]);

        Product::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Total Revenue');
        $response->assertSee('Total Orders');
        $response->assertSee('Recent Orders');
    }
}
