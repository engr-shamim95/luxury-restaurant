<?php

namespace Tests\Feature\E2E\Tier3_CrossFeatureCombinations;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsToStorefrontCombinationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Owner',
            'email' => 'owner@restaurant.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);
    }

    public function test_dynamic_store_rebranding_propagates_to_storefront_views(): void
    {
        // 1. Initial default settings
        Setting::create(['key' => 'restaurant_name', 'value' => 'Initial Trattoria', 'type' => 'string']);
        Setting::create(['key' => 'hero_title', 'value' => 'Original Welcome', 'type' => 'string']);

        $home1 = $this->get('/');
        $home1->assertSee('Initial Trattoria');
        $home1->assertSee('Original Welcome');

        // 2. Admin performs rebranding update
        $this->actingAs($this->admin)->put('/admin/settings', [
            'restaurant_name' => 'Osteria Del Sole',
            'hero_title' => 'Sun-Drenched Tuscan Flavours',
            'restaurant_phone' => '+1 (555) 999-8888',
        ])->assertRedirect();

        // 3. Customer visits homepage and verifies updated identity
        $home2 = $this->get('/');
        $home2->assertSee('Osteria Del Sole');
        $home2->assertSee('Sun-Drenched Tuscan Flavours');
        $home2->assertDontSee('Initial Trattoria');
    }

    public function test_tax_rate_change_in_settings_modifies_checkout_calculations(): void
    {
        $category = Category::create(['name' => 'Food', 'slug' => 'food', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Pasta Bowl',
            'slug' => 'pasta-bowl',
            'base_price' => 50.00,
            'is_available' => true,
        ]);

        $cart = [
            'item_1' => [
                'item_key' => 'item_1',
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => 50.00,
                'quantity' => 1,
            ],
        ];

        // 1. Set tax to 10%
        Setting::create(['key' => 'tax_rate', 'value' => '10', 'type' => 'string']);
        Setting::create(['key' => 'delivery_fee', 'value' => '0.00', 'type' => 'string']);

        // Check tax calculation in checkout submission (50 * 10% = 5.00, Total = 55.00)
        $this->withSession(['cart' => $cart])->post('/checkout', [
            'customer_name' => 'Customer A',
            'customer_email' => 'a@test.com',
            'customer_phone' => '555-1111',
            'order_type' => 'pickup',
            'payment_method' => 'cash',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'customer_email' => 'a@test.com',
            'subtotal' => 50.00,
            'tax' => 5.00,
            'total' => 55.00,
        ]);

        // 2. Admin updates tax to 20%
        $this->actingAs($this->admin)->put('/admin/settings', [
            'tax_rate' => '20',
        ])->assertRedirect();

        // Customer B places same order (50 * 20% = 10.00, Total = 60.00)
        $this->withSession(['cart' => $cart])->post('/checkout', [
            'customer_name' => 'Customer B',
            'customer_email' => 'b@test.com',
            'customer_phone' => '555-2222',
            'order_type' => 'pickup',
            'payment_method' => 'cash',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'customer_email' => 'b@test.com',
            'subtotal' => 50.00,
            'tax' => 10.00,
            'total' => 60.00,
        ]);
    }
}
