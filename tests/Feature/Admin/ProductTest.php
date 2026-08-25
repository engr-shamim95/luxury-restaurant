<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_products_list_and_filter(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->get(route('admin.products.index', ['category_id' => $category->id]));

        $response->assertStatus(200);
        $response->assertSee('Products & Dishes Catalog');
    }

    public function test_admin_can_create_product_with_variants(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $productData = [
            'category_id' => $category->id,
            'name' => 'Signature Truffle Gnocchi',
            'slug' => 'signature-truffle-gnocchi',
            'description' => 'Potato gnocchi in rich truffle cream sauce.',
            'base_price' => '18.50',
            'is_available' => '1',
            'variants' => [
                ['name' => 'Regular', 'type' => 'size', 'price_adjustment' => '0.00'],
                ['name' => 'Large Platter', 'type' => 'size', 'price_adjustment' => '5.00'],
                ['name' => 'Extra Shaved Truffles', 'type' => 'addon', 'price_adjustment' => '4.50'],
            ],
        ];

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $productData);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Signature Truffle Gnocchi',
            'base_price' => 18.50,
            'has_variants' => 1,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'name' => 'Large Platter',
            'price_adjustment' => 5.00,
        ]);
    }

    public function test_admin_can_update_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['name' => 'Old Dish Name']);

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $product->category_id,
            'name' => 'New Gourmet Dish',
            'slug' => $product->slug,
            'base_price' => '22.00',
            'is_available' => '1',
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Gourmet Dish',
            'base_price' => 22.00,
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_admin_can_add_and_delete_product_variant(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        // Add variant
        $response = $this->actingAs($admin)->post(route('admin.products.variants.store', $product), [
            'name' => 'Extra Spicy',
            'type' => 'spice_level',
            'price_adjustment' => '1.50',
            'is_active' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'name' => 'Extra Spicy',
        ]);

        $variant = ProductVariant::where('product_id', $product->id)->first();

        // Delete variant
        $delResponse = $this->actingAs($admin)->delete(route('admin.variants.destroy', $variant));
        $delResponse->assertRedirect();
        $this->assertDatabaseMissing('product_variants', [
            'id' => $variant->id,
        ]);
    }
}
