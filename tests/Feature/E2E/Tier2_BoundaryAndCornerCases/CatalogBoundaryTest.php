<?php

namespace Tests\Feature\E2E\Tier2_BoundaryAndCornerCases;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CatalogBoundaryTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin Manager',
            'email' => 'admin@restaurant.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Specials',
            'slug' => 'specials',
            'is_active' => true,
        ]);
    }

    public function test_negative_product_base_price_fails_validation(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/products', [
            'category_id' => $this->category->id,
            'name' => 'Negative Price Pizza',
            'slug' => 'negative-price-pizza',
            'base_price' => -10.00,
            'is_available' => 1,
        ]);

        $response->assertSessionHasErrors(['base_price']);
        $this->assertDatabaseMissing('products', ['slug' => 'negative-price-pizza']);
    }

    public function test_zero_base_price_allowed_for_free_promotions(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/products', [
            'category_id' => $this->category->id,
            'name' => 'Complimentary Breadsticks',
            'slug' => 'complimentary-breadsticks',
            'base_price' => 0.00,
            'is_available' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'slug' => 'complimentary-breadsticks',
            'base_price' => 0.00,
        ]);
    }

    public function test_negative_variant_price_adjustment_allowed_for_discounts(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Custom Pasta Bowl',
            'slug' => 'custom-pasta-bowl',
            'base_price' => 14.00,
            'is_available' => true,
            'has_variants' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->post("/admin/products/{$product->id}/variants", [
            'product_id' => $product->id,
            'name' => 'Half Portion Discount',
            'type' => 'size',
            'price_adjustment' => -3.50,
            'is_active' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'name' => 'Half Portion Discount',
            'price_adjustment' => -3.50,
        ]);
    }

    public function test_inactive_category_products_are_hidden_from_active_menu_view(): void
    {
        $hiddenCategory = Category::create([
            'name' => 'Hidden Secret Category',
            'slug' => 'hidden-secret',
            'is_active' => false,
        ]);

        Product::create([
            'category_id' => $hiddenCategory->id,
            'name' => 'Secret VIP Truffle Dish',
            'slug' => 'secret-vip-truffle',
            'base_price' => 99.00,
            'is_available' => true,
        ]);

        $response = $this->get('/menu');

        $response->assertStatus(200);
        $response->assertDontSee('Secret VIP Truffle Dish');
    }

    public function test_products_with_empty_descriptions_render_without_errors(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'name' => 'Simple Water',
            'slug' => 'simple-water',
            'description' => null,
            'base_price' => 2.00,
            'is_available' => true,
        ]);

        $response = $this->get('/menu');

        $response->assertStatus(200);
        $response->assertSee('Simple Water');
    }
}
