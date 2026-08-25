<?php

namespace Tests\Feature\E2E\Tier1_FeatureCoverage;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProductVariantsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin Manager',
            'email' => 'admin@restaurant.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        $category = Category::create([
            'name' => 'Pizzas',
            'slug' => 'pizzas',
            'is_active' => true,
            'order' => 1,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Quattro Formaggi',
            'slug' => 'quattro-formaggi',
            'base_price' => 15.00,
            'is_available' => true,
            'has_variants' => true,
        ]);
    }

    public function test_admin_can_add_size_variant_to_product(): void
    {
        $payload = [
            'product_id' => $this->product->id,
            'name' => 'Large 16"',
            'type' => 'size',
            'price_adjustment' => 4.50,
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->adminUser)->post("/admin/products/{$this->product->id}/variants", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $this->product->id,
            'name' => 'Large 16"',
            'type' => 'size',
            'price_adjustment' => 4.50,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_add_addon_variant_with_price_adjustment(): void
    {
        $payload = [
            'product_id' => $this->product->id,
            'name' => 'Extra Truffle Oil',
            'type' => 'addon',
            'price_adjustment' => 2.50,
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->adminUser)->post("/admin/products/{$this->product->id}/variants", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $this->product->id,
            'name' => 'Extra Truffle Oil',
            'type' => 'addon',
            'price_adjustment' => 2.50,
        ]);
    }

    public function test_admin_can_update_product_variant(): void
    {
        $variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'name' => 'Medium 12"',
            'type' => 'size',
            'price_adjustment' => 2.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->put("/admin/variants/{$variant->id}", [
            'product_id' => $this->product->id,
            'name' => 'Medium 14" (Updated)',
            'type' => 'size',
            'price_adjustment' => 3.00,
            'is_active' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'name' => 'Medium 14" (Updated)',
            'price_adjustment' => 3.00,
        ]);
    }

    public function test_admin_can_toggle_product_variant_active_status(): void
    {
        $variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'name' => 'Seasonal Topping',
            'type' => 'addon',
            'price_adjustment' => 1.50,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->put("/admin/variants/{$variant->id}", [
            'product_id' => $this->product->id,
            'name' => 'Seasonal Topping',
            'type' => 'addon',
            'price_adjustment' => 1.50,
            'is_active' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_product_variant(): void
    {
        $variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'name' => 'Temporary Promo Size',
            'type' => 'size',
            'price_adjustment' => 0.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->delete("/admin/variants/{$variant->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
    }
}
