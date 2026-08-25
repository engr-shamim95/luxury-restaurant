<?php

namespace Tests\Feature\E2E\Tier2_BoundaryAndCornerCases;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityAndAccessBoundaryTest extends TestCase
{
    use RefreshDatabase;

    protected User $regularUser;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regularUser = User::create([
            'name' => 'Regular Customer',
            'email' => 'customer@restaurant.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin Boss',
            'email' => 'boss@restaurant.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login_on_admin_routes(): void
    {
        $adminEndpoints = [
            '/admin',
            '/admin/settings',
            '/admin/categories',
            '/admin/products',
            '/admin/pages',
            '/admin/navigation',
            '/admin/orders',
            '/admin/system',
        ];

        foreach ($adminEndpoints as $endpoint) {
            $response = $this->get($endpoint);
            $response->assertRedirect('/login');
        }
    }

    public function test_regular_authenticated_user_is_redirected_away_from_admin_routes(): void
    {
        $adminEndpoints = [
            '/admin',
            '/admin/settings',
            '/admin/categories',
            '/admin/products',
            '/admin/orders',
            '/admin/system',
        ];

        foreach ($adminEndpoints as $endpoint) {
            $response = $this->actingAs($this->regularUser)->get($endpoint);
            $response->assertRedirect('/');
        }
    }

    public function test_regular_user_cannot_mutate_admin_settings_or_data(): void
    {
        $response = $this->actingAs($this->regularUser)->post('/admin/categories', [
            'name' => 'Malicious Category',
            'slug' => 'malicious-category',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseMissing('categories', ['slug' => 'malicious-category']);
    }

    public function test_regular_user_cannot_execute_system_artisan_commands(): void
    {
        $response = $this->actingAs($this->regularUser)->post('/admin/system/run', [
            'command' => 'cache:clear',
        ]);

        $response->assertRedirect('/');
    }

    public function test_admin_can_successfully_access_all_admin_endpoints(): void
    {
        $adminEndpoints = [
            '/admin',
            '/admin/settings',
            '/admin/categories',
            '/admin/products',
            '/admin/pages',
            '/admin/navigation',
            '/admin/orders',
            '/admin/system',
        ];

        foreach ($adminEndpoints as $endpoint) {
            $response = $this->actingAs($this->adminUser)->get($endpoint);
            $response->assertStatus(200);
        }
    }
}
