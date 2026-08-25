<?php

namespace Tests\Feature\E2E\Tier1_FeatureCoverage;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendMenuTest extends TestCase
{
    use RefreshDatabase;

    protected Category $activeCategory;
    protected Category $inactiveCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activeCategory = Category::create([
            'name' => 'Signature Pizzas',
            'slug' => 'signature-pizzas',
            'is_active' => true,
            'order' => 1,
        ]);

        $this->inactiveCategory = Category::create([
            'name' => 'Secret Menu',
            'slug' => 'secret-menu',
            'is_active' => false,
            'order' => 99,
        ]);
    }

    public function test_menu_page_returns_successful_response(): void
    {
        $response = $this->get('/menu');

        $response->assertStatus(200);
    }

    public function test_menu_page_displays_all_active_categories(): void
    {
        $response = $this->get('/menu');

        $response->assertStatus(200);
        $response->assertSee('Signature Pizzas');
        $response->assertDontSee('Secret Menu');
    }

    public function test_menu_page_displays_available_products_with_base_prices(): void
    {
        Product::create([
            'category_id' => $this->activeCategory->id,
            'name' => 'Diavola Spicy Salami',
            'slug' => 'diavola-spicy-salami',
            'description' => 'Calabrian chili, spicy soppressata, mozzarella.',
            'base_price' => 16.50,
            'is_available' => true,
            'has_variants' => false,
        ]);

        $response = $this->get('/menu');

        $response->assertStatus(200);
        $response->assertSee('Diavola Spicy Salami');
        $response->assertSee('16.50');
    }

    public function test_menu_page_displays_product_variant_options(): void
    {
        $product = Product::create([
            'category_id' => $this->activeCategory->id,
            'name' => 'Capricciosa Deluxe',
            'slug' => 'capricciosa-deluxe',
            'base_price' => 17.00,
            'is_available' => true,
            'has_variants' => true,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Large 16" (+ $5.00)',
            'type' => 'size',
            'price_adjustment' => 5.00,
            'is_active' => true,
        ]);

        $response = $this->get('/menu');

        $response->assertStatus(200);
        $response->assertSee('Capricciosa Deluxe');
        $response->assertSee('Large 16"');
    }

    public function test_menu_page_does_not_display_unavailable_products(): void
    {
        Product::create([
            'category_id' => $this->activeCategory->id,
            'name' => 'Sold Out Lobster Pizza',
            'slug' => 'sold-out-lobster-pizza',
            'base_price' => 35.00,
            'is_available' => false,
            'has_variants' => false,
        ]);

        $response = $this->get('/menu');

        $response->assertStatus(200);
        $response->assertDontSee('Sold Out Lobster Pizza');
    }

    public function test_menu_page_can_filter_by_category_slug(): void
    {
        $pastas = Category::create([
            'name' => 'Fresh Pastas',
            'slug' => 'fresh-pastas',
            'is_active' => true,
            'order' => 2,
        ]);

        Product::create([
            'category_id' => $this->activeCategory->id,
            'name' => 'Pizza Margherita',
            'slug' => 'pizza-margherita',
            'base_price' => 12.00,
            'is_available' => true,
        ]);

        Product::create([
            'category_id' => $pastas->id,
            'name' => 'Tagliatelle al Ragù',
            'slug' => 'tagliatelle-ragu',
            'base_price' => 15.00,
            'is_available' => true,
        ]);

        $response = $this->get('/menu?category=fresh-pastas');

        $response->assertStatus(200);
        $response->assertSee('Tagliatelle al Ragù');
    }
}
