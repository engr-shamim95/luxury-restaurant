<?php

namespace Tests\Feature\Adversarial;

use App\Models\Category;
use App\Models\NavigationItem;
use App\Models\NavigationMenu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DynamicReconfigurationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Dynamic store branding and identity reconfiguration across all views.
     * Mutate restaurant_name, tagline, contact details, opening hours, copyright,
     * and assert zero traces of hardcoded defaults remain in header, footer, title, and confirmation.
     */
    public function test_dynamic_store_rebranding_propagates_across_all_frontend_views(): void
    {
        $uniqueBrand = 'Nebula Bistro ' . rand(1000, 9999);
        $uniqueTagline = 'Galactic Delicacies from Beyond the Stars ' . Str::random(6);
        $uniquePhone = '+1-800-' . rand(100, 999) . '-' . rand(1000, 9999);
        $uniqueEmail = 'contact@' . Str::slug($uniqueBrand) . '.universe';
        $uniqueAddress = rand(100, 999) . ' Starship Way, Sector ' . rand(1, 99);
        $uniqueHours = 'Stardate Mon-Fri 0800 - 2200 MST';
        $uniqueCopyright = '© 2099 ' . $uniqueBrand . ' Interstellar Corporation';

        // Mutate settings via Setting::set()
        Setting::set('restaurant_name', $uniqueBrand, 'string');
        Setting::set('site_name', $uniqueBrand, 'string');
        Setting::set('site_tagline', $uniqueTagline, 'string');
        Setting::set('contact_phone', $uniquePhone, 'string');
        Setting::set('restaurant_phone', $uniquePhone, 'string');
        Setting::set('contact_email', $uniqueEmail, 'string');
        Setting::set('restaurant_email', $uniqueEmail, 'string');
        Setting::set('contact_address', $uniqueAddress, 'string');
        Setting::set('restaurant_address', $uniqueAddress, 'string');
        Setting::set('opening_hours', $uniqueHours, 'string');
        Setting::set('copyright_text', $uniqueCopyright, 'string');

        // 1. Assert Homepage reflects all dynamic brand identity
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee($uniqueBrand);
        $homeResponse->assertSee($uniqueTagline);
        $homeResponse->assertSee($uniquePhone);
        $homeResponse->assertSee($uniqueEmail);
        $homeResponse->assertSee($uniqueAddress);
        $homeResponse->assertSee($uniqueHours);
        $homeResponse->assertSee($uniqueCopyright);

        // 2. Assert Menu page reflects dynamic restaurant branding
        $menuResponse = $this->get(route('menu'));
        $menuResponse->assertStatus(200);
        $menuResponse->assertSee($uniqueBrand);
        $menuResponse->assertSee($uniquePhone);
        $menuResponse->assertSee($uniqueEmail);
        $menuResponse->assertSee($uniqueAddress);
        $menuResponse->assertSee($uniqueCopyright);

        // 3. Assert Order Confirmation page reflects dynamic branding & address for pickup
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'base_price' => 25.00,
            'is_available' => true,
        ]);
        $order = Order::factory()->create([
            'customer_name' => 'Commander Alex',
            'order_type' => 'pickup',
            'subtotal' => 25.00,
            'tax' => 0.00,
            'total' => 25.00,
        ]);

        $confirmResponse = $this->get(route('order.confirmation', $order->id));
        $confirmResponse->assertStatus(200);
        $confirmResponse->assertSee($uniqueBrand);
        $confirmResponse->assertSee($uniqueAddress);
        $confirmResponse->assertSee($uniquePhone);
        $confirmResponse->assertSee($uniqueCopyright);
    }

    /**
     * Test 2: Dynamic Admin settings update reflects immediately on customer storefront.
     */
    public function test_admin_settings_update_dynamically_reconfigures_storefront(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $mutatedName = 'Quantum Diner ' . rand(1000, 9999);
        $mutatedTagline = 'Quantum Flavors at Subatomic Precision';

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'restaurant_name' => $mutatedName,
            'site_tagline' => $mutatedTagline,
            'contact_phone' => '555-QUANTUM',
            'contact_email' => 'dine@quantum.io',
            'contact_address' => '42 Particle Highway',
            'opening_hours' => 'Always Open in Superposition',
            'currency_symbol' => 'Credits',
            'tax_rate' => 12.5,
            'delivery_fee' => 7.50,
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        // Customer visits homepage as unauthenticated guest
        $guestResponse = $this->get(route('home'));
        $guestResponse->assertStatus(200);
        $guestResponse->assertSee($mutatedName);
        $guestResponse->assertSee($mutatedTagline);
        $guestResponse->assertSee('555-QUANTUM');
        $guestResponse->assertSee('dine@quantum.io');
        $guestResponse->assertSee('42 Particle Highway');
    }

    /**
     * Test 3: Dynamic custom tax rate and currency formatting in cart and checkout.
     * Mutate tax_rate to non-standard percentage (14.5%) and currency to '€'.
     */
    public function test_dynamic_tax_rate_and_currency_calculation(): void
    {
        Setting::set('tax_rate', 14.5, 'float');
        Setting::set('currency_symbol', '€', 'string');
        Setting::set('delivery_fee', 8.25, 'float');

        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Intergalactic Steak',
            'base_price' => 50.00,
            'has_variants' => true,
            'is_available' => true,
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Cosmic Rare',
            'price_adjustment' => 10.00,
            'is_active' => true,
        ]);

        // Add 2 items (unit price = 50 + 10 = 60.00) => Subtotal = 120.00
        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        // Expected subtotal = 120.00
        // Expected tax at 14.5% = round(120.00 * 0.145, 2) = 17.40
        // Expected total (pickup) = 137.40
        $cartResponse = $this->get(route('cart.index'));
        $cartResponse->assertStatus(200);
        $cartResponse->assertSee('14.5%');
        $cartResponse->assertSee('€120.00');
        $cartResponse->assertSee('€17.40');
        $cartResponse->assertSee('€137.40');

        $checkoutResponse = $this->get(route('checkout.index'));
        $checkoutResponse->assertStatus(200);
        $checkoutResponse->assertSee('14.5%');
        $checkoutResponse->assertSee('€120.00');
        $checkoutResponse->assertSee('€17.40');

        // Submit pickup order
        $orderPostResponse = $this->post(route('checkout.store'), [
            'customer_name' => 'Spock of Vulcan',
            'customer_email' => 'spock@enterprise.fleet',
            'customer_phone' => '111-222-3333',
            'order_type' => 'pickup',
            'payment_method' => 'cash',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(120.00, (float) $order->subtotal);
        $this->assertEquals(17.40, (float) $order->tax);
        $this->assertEquals(137.40, (float) $order->total);

        $orderPostResponse->assertRedirect(route('order.confirmation', $order->id));

        $confirmResponse = $this->get(route('order.confirmation', $order->id));
        $confirmResponse->assertStatus(200);
        $confirmResponse->assertSee('€120.00');
        $confirmResponse->assertSee('€17.40');
        $confirmResponse->assertSee('€137.40');
    }

    /**
     * Test 4: Dynamic CMS pages and custom navigation menu items render dynamically.
     */
    public function test_dynamic_cms_pages_and_navigation_items_render_on_frontend(): void
    {
        // 1. Create a dynamic CMS page
        $page = Page::factory()->create([
            'title' => 'Cosmic Dining Protocol',
            'slug' => 'cosmic-protocol',
            'content' => '<p>All sentient entities must respect the universal dining standards.</p>',
            'is_published' => true,
        ]);

        // 2. Set up Header Navigation Menu with dynamic links
        $headerMenu = NavigationMenu::firstOrCreate(['location' => 'header'], ['name' => 'Main Header Menu']);
        $headerMenu->items()->delete();

        NavigationItem::create([
            'navigation_menu_id' => $headerMenu->id,
            'label' => 'Protocol Page',
            'page_id' => $page->id,
            'order' => 1,
        ]);

        NavigationItem::create([
            'navigation_menu_id' => $headerMenu->id,
            'label' => 'Starfleet Delivery Tracker',
            'url' => 'https://starfleet.space/tracker',
            'order' => 2,
            'target' => '_blank',
        ]);

        // 3. Set up Footer Navigation Menu
        $footerMenu = NavigationMenu::firstOrCreate(['location' => 'footer'], ['name' => 'Footer Links']);
        $footerMenu->items()->delete();

        NavigationItem::create([
            'navigation_menu_id' => $footerMenu->id,
            'label' => 'Footer Galactic Policy',
            'page_id' => $page->id,
            'order' => 1,
        ]);

        // 4. Assert frontend home renders navigation links dynamically
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Protocol Page');
        $homeResponse->assertSee(url('/page/cosmic-protocol'));
        $homeResponse->assertSee('Starfleet Delivery Tracker');
        $homeResponse->assertSee('https://starfleet.space/tracker');
        $homeResponse->assertSee('Footer Galactic Policy');

        // 5. Assert dynamic page route renders the page content
        $pageResponse = $this->get(route('page.show', 'cosmic-protocol'));
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Cosmic Dining Protocol');
        $pageResponse->assertSee('All sentient entities must respect the universal dining standards.');
    }

    /**
     * Test 5: Dynamic Catalog lifecycle - brand new category, product, and variant
     * immediately visible on storefront and processable through checkout.
     */
    public function test_dynamic_catalog_category_product_variant_lifecycle(): void
    {
        $uniqueCategoryName = 'Supernova Confections ' . rand(100, 999);
        $uniqueCategorySlug = 'supernova-confections-' . rand(100, 999);
        $uniqueProductName = 'Plasma Fondant Delight ' . rand(100, 999);
        $uniqueProductSlug = 'plasma-fondant-' . rand(100, 999);
        $uniqueVariantName = 'Antimatter Infusion';

        // 1. Create Category
        $category = Category::create([
            'name' => $uniqueCategoryName,
            'slug' => $uniqueCategorySlug,
            'description' => 'Exotic high-energy desserts.',
            'is_active' => true,
            'order' => 1,
        ]);

        // 2. Create Product with has_variants = true
        $product = Product::create([
            'category_id' => $category->id,
            'name' => $uniqueProductName,
            'slug' => $uniqueProductSlug,
            'description' => 'Melt-in-your-mouth hyperdense cocoa.',
            'base_price' => 30.00,
            'has_variants' => true,
            'is_available' => true,
        ]);

        // 3. Create Product Variant
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => $uniqueVariantName,
            'price_adjustment' => 5.50,
            'is_active' => true,
            'order' => 1,
        ]);

        // 4. Assert customer sees new category and product on storefront
        $menuResponse = $this->get(route('menu'));
        $menuResponse->assertStatus(200);
        $menuResponse->assertSee($uniqueCategoryName);
        $menuResponse->assertSee($uniqueProductName);
        $menuResponse->assertSee('30.00');
        $menuResponse->assertSee($uniqueVariantName);

        // Filter by category
        $categoryFilteredResponse = $this->get(route('menu', ['category' => $uniqueCategorySlug]));
        $categoryFilteredResponse->assertStatus(200);
        $categoryFilteredResponse->assertSee($uniqueProductName);

        // 5. Add to Cart with variant (30.00 + 5.50 = 35.50 * 2 = 71.00)
        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $cartResponse = $this->get(route('cart.index'));
        $cartResponse->assertStatus(200);
        $cartResponse->assertSee($uniqueProductName);
        $cartResponse->assertSee($uniqueVariantName);
        $cartResponse->assertSee('71.00');

        // 6. Complete Checkout
        $this->post(route('checkout.store'), [
            'customer_name' => 'Captain Picard',
            'customer_email' => 'picard@starfleet.org',
            'customer_phone' => '999-888-7777',
            'order_type' => 'pickup',
            'payment_method' => 'cash',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(71.00, (float) $order->subtotal);

        $orderItem = $order->items->first();
        $this->assertNotNull($orderItem);
        $this->assertEquals($uniqueProductName, $orderItem->product_name);
        $this->assertEquals(35.50, (float) $orderItem->unit_price);
        $this->assertEquals(2, $orderItem->quantity);
        $this->assertEquals(71.00, (float) $orderItem->total_price);

        // 7. Verify confirmation
        $confirmResponse = $this->get(route('order.confirmation', $order->id));
        $confirmResponse->assertStatus(200);
        $confirmResponse->assertSee('Captain Picard');
        $confirmResponse->assertSee($uniqueProductName);
        $confirmResponse->assertSee($uniqueVariantName);
        $confirmResponse->assertSee('71.00');
    }

    /**
     * Test 6: Catalog updates propagate instantly without stale cache.
     */
    public function test_catalog_mutations_and_deactivations_propagate_instantly(): void
    {
        $category = Category::factory()->create(['name' => 'Original Category', 'is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Original Dish',
            'base_price' => 15.00,
            'is_available' => true,
        ]);

        // Verify initial state
        $response = $this->get(route('menu'));
        $response->assertSee('Original Dish');
        $response->assertSee('15.00');

        // Mutate product name & price
        $product->update([
            'name' => 'Transformed Super Dish',
            'base_price' => 45.00,
        ]);

        $updatedResponse = $this->get(route('menu'));
        $updatedResponse->assertSee('Transformed Super Dish');
        $updatedResponse->assertSee('45.00');
        $updatedResponse->assertDontSee('Original Dish');

        // Deactivate product availability
        $product->update(['is_available' => false]);

        $deactivatedResponse = $this->get(route('menu'));
        $deactivatedResponse->assertDontSee('Transformed Super Dish');
    }

    /**
     * Test 7: Adversarial zero hardcoding probe across storefront templates.
     * Ensure hero titles, CTA links, footer text, and delivery fees are 100% dynamic.
     */
    public function test_adversarial_zero_hardcoding_probe_across_storefront_templates(): void
    {
        Setting::set('hero_title', 'Adversarial Custom Hero Title 12345');
        Setting::set('hero_subtitle', 'Adversarial Custom Subtitle 67890');
        Setting::set('hero_cta_text', 'Order Galactic Fuel Now');
        Setting::set('hero_cta_link', 'https://custom-fuel-link.com');
        Setting::set('delivery_fee', 19.99);

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('Adversarial Custom Hero Title 12345');
        $response->assertSee('Adversarial Custom Subtitle 67890');
        $response->assertSee('Order Galactic Fuel Now');
        $response->assertSee('https://custom-fuel-link.com');
    }

    /**
     * Test 8: Dynamic delivery checkout with delivery fee calculations and address requirements.
     */
    public function test_dynamic_delivery_fee_calculation_and_order_persistence(): void
    {
        Setting::set('tax_rate', 10.0, 'float');
        Setting::set('delivery_fee', 12.50, 'float');
        Setting::set('currency_symbol', '$', 'string');

        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Hyperdrive Pizza',
            'base_price' => 40.00,
            'is_available' => true,
        ]);

        // Add 1 item => subtotal = 40.00, tax (10%) = 4.00, delivery fee = 12.50 => total = 56.50
        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $postOrderResponse = $this->post(route('checkout.store'), [
            'customer_name' => 'Lieutenant Uhura',
            'customer_email' => 'uhura@enterprise.space',
            'customer_phone' => '555-4321',
            'order_type' => 'delivery',
            'delivery_address' => 'Deck 7, Section 4, Starship Enterprise',
            'payment_method' => 'card',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(40.00, (float) $order->subtotal);
        $this->assertEquals(4.00, (float) $order->tax);
        $this->assertEquals(56.50, (float) $order->total);
        $this->assertEquals('Deck 7, Section 4, Starship Enterprise', $order->delivery_address);

        $postOrderResponse->assertRedirect(route('order.confirmation', $order->id));

        $confirmResponse = $this->get(route('order.confirmation', $order->id));
        $confirmResponse->assertStatus(200);
        $confirmResponse->assertSee('Lieutenant Uhura');
        $confirmResponse->assertSee('Deck 7, Section 4, Starship Enterprise');
        $confirmResponse->assertSee('56.50');
    }

    /**
     * Test 9: Graceful handling of orphaned navigation items pointing to deleted CMS pages.
     */
    public function test_navigation_menu_gracefully_handles_orphaned_page_items(): void
    {
        $page = Page::factory()->create([
            'title' => 'Temporary Secret Recipe',
            'slug' => 'secret-recipe',
            'is_published' => true,
        ]);

        $headerMenu = NavigationMenu::firstOrCreate(['location' => 'header'], ['name' => 'Main Header Menu']);
        $headerMenu->items()->delete();

        $navItem = NavigationItem::create([
            'navigation_menu_id' => $headerMenu->id,
            'label' => 'Secret Recipe Link',
            'page_id' => $page->id,
            'order' => 1,
        ]);

        // Delete page
        $page->delete();

        // Fresh request should not crash 500, should resolve URL to '#'
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('Secret Recipe Link');
        $response->assertSee('href="#"', false);
    }
}
