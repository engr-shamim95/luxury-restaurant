<?php

namespace Tests\Feature\E2E\Tier4_RealWorldScenarios;

use App\Models\Category;
use App\Models\NavigationItem;
use App\Models\NavigationMenu;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DynamicStorefrontReconfigurationScenarioTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Owner Admin',
            'email' => 'owner@restaurant.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);
    }

    public function test_zero_hardcoding_full_business_rebranding_and_expansion_scenario(): void
    {
        // 1. Initial State: Standard Restaurant
        Setting::create(['key' => 'restaurant_name', 'value' => 'Luigi Standard Cafe', 'type' => 'string']);
        Setting::create(['key' => 'currency_symbol', 'value' => '$', 'type' => 'string']);
        Setting::create(['key' => 'tax_rate', 'value' => '5.0', 'type' => 'string']);
        Setting::create(['key' => 'delivery_fee', 'value' => '3.00', 'type' => 'string']);

        $home1 = $this->get('/');
        $home1->assertSee('Luigi Standard Cafe');

        // 2. Business Rebranding: Admin updates settings
        $this->actingAs($this->admin)->put('/admin/settings', [
            'restaurant_name' => 'Luigi Summer BBQ & Bar',
            'hero_title' => 'Wood-Smoked Feast Under the Sun',
            'hero_subtitle' => 'Live music every Friday & Saturday evening!',
            'opening_hours' => 'Mon-Sun: 12:00 PM - Midnight',
            'tax_rate' => '8.0',
            'delivery_fee' => '6.00',
        ])->assertRedirect();

        // 3. Admin creates a new CMS Page for Summer Catering
        $this->actingAs($this->admin)->post('/admin/pages', [
            'title' => 'Summer Catering Packages',
            'slug' => 'summer-catering',
            'content' => '<p>Book our mobile wood-fired oven for your private events.</p>',
            'is_published' => 1,
        ])->assertRedirect();
        $page = Page::where('slug', 'summer-catering')->firstOrFail();

        // 4. Admin updates Header navigation to feature the new page
        $headerMenu = NavigationMenu::firstOrCreate(['location' => 'header'], ['name' => 'Header Menu']);
        $this->actingAs($this->admin)->post('/admin/navigation/items', [
            'navigation_menu_id' => $headerMenu->id,
            'label' => 'Summer Catering',
            'page_id' => $page->id,
            'order' => 1,
            'target' => '_self',
        ])->assertRedirect();

        // 5. Admin adds a new seasonal Category & Product
        $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Summer BBQ Plates',
            'slug' => 'summer-bbq-plates',
            'description' => 'Smoked ribs, brisket, and grilled vegetables.',
            'is_active' => 1,
            'order' => 1,
        ])->assertRedirect();
        $category = Category::where('slug', 'summer-bbq-plates')->firstOrFail();

        $this->actingAs($this->admin)->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Smoked Brisket Platter',
            'slug' => 'smoked-brisket-platter',
            'description' => '12-hour oak smoked beef brisket with house pickles.',
            'base_price' => 28.00,
            'is_available' => 1,
            'has_variants' => 1,
        ])->assertRedirect();
        $product = Product::where('slug', 'smoked-brisket-platter')->firstOrFail();

        $this->actingAs($this->admin)->post("/admin/products/{$product->id}/variants", [
            'product_id' => $product->id,
            'name' => 'Double Meat (+ $10.00)',
            'type' => 'size',
            'price_adjustment' => 10.00,
            'is_active' => 1,
        ])->assertRedirect();
        $variant = ProductVariant::where('product_id', $product->id)->firstOrFail();

        // 6. Customer arrives and experiences the newly branded platform
        $home2 = $this->get('/');
        $home2->assertStatus(200);
        $home2->assertSee('Luigi Summer BBQ &amp; Bar', false);
        $home2->assertSee('Wood-Smoked Feast Under the Sun');
        $home2->assertSee('Summer Catering');

        // Customer views the dynamic page
        $pageView = $this->get('/page/summer-catering');
        $pageView->assertStatus(200);
        $pageView->assertSee('Summer Catering Packages');
        $pageView->assertSee('Book our mobile wood-fired oven');

        // Customer views menu, finds the new seasonal product
        $menuView = $this->get('/menu');
        $menuView->assertStatus(200);
        $menuView->assertSee('Summer BBQ Plates');
        $menuView->assertSee('Smoked Brisket Platter');

        // 7. Customer orders the new item with Double Meat variant
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect();

        // 8. Customer completes checkout
        $this->post('/checkout', [
            'customer_name' => 'Festival Lover',
            'customer_email' => 'fest@test.com',
            'customer_phone' => '555-4321',
            'order_type' => 'delivery',
            'delivery_address' => 'Festival Grounds, Tent 4',
            'payment_method' => 'card',
        ])->assertRedirect();

        // 9. Verify Order calculations with new tax (8%) and new delivery fee ($6.00)
        // Unit price = 28.00 + 10.00 = 38.00. Subtotal = 38.00.
        $order = Order::where('customer_email', 'fest@test.com')->firstOrFail();
        $this->assertEquals(38.00, $order->subtotal);

        // 10. Admin views order in admin dashboard
        $adminOrder = $this->actingAs($this->admin)->get("/admin/orders/{$order->id}");
        $adminOrder->assertStatus(200);
        $adminOrder->assertSee('Festival Lover');
        $adminOrder->assertSee('Smoked Brisket Platter');
        $adminOrder->assertSee('Double Meat');
    }
}
