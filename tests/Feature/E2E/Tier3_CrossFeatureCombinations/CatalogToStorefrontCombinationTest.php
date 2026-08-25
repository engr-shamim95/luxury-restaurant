<?php

namespace Tests\Feature\E2E\Tier3_CrossFeatureCombinations;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CatalogToStorefrontCombinationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Chef',
            'email' => 'chef@restaurant.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);
    }

    public function test_admin_created_catalog_immediately_renders_on_customer_menu(): void
    {
        // 1. Admin creates Category
        $catResponse = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Artisan Risottos',
            'slug' => 'artisan-risottos',
            'description' => 'Slow-cooked Carnaroli rice specialties',
            'is_active' => 1,
            'order' => 1,
        ]);
        $catResponse->assertRedirect();
        $category = Category::where('slug', 'artisan-risottos')->firstOrFail();

        // 2. Admin creates Product in that Category
        $prodResponse = $this->actingAs($this->admin)->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Risotto ai Frutti di Mare',
            'slug' => 'risotto-frutti-di-mare',
            'description' => 'Clams, mussels, calamari, and saffron broth.',
            'base_price' => 22.50,
            'is_available' => 1,
            'has_variants' => 1,
        ]);
        $prodResponse->assertRedirect();
        $product = Product::where('slug', 'risotto-frutti-di-mare')->firstOrFail();

        // 3. Admin adds Variants
        $varResponse = $this->actingAs($this->admin)->post("/admin/products/{$product->id}/variants", [
            'product_id' => $product->id,
            'name' => 'Add Jumbo Prawns',
            'type' => 'addon',
            'price_adjustment' => 6.00,
            'is_active' => 1,
        ]);
        $varResponse->assertRedirect();

        // 4. Customer visits /menu and verifies rendering
        $menuResponse = $this->get('/menu');
        $menuResponse->assertStatus(200);
        $menuResponse->assertSee('Artisan Risottos');
        $menuResponse->assertSee('Risotto ai Frutti di Mare');
        $menuResponse->assertSee('22.50');
        $menuResponse->assertSee('Add Jumbo Prawns');
    }

    public function test_admin_catalog_mutations_reflect_dynamically_without_stale_data(): void
    {
        $category = Category::create([
            'name' => 'Dessert Corner',
            'slug' => 'dessert-corner',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Tiramisu Tradizionale',
            'slug' => 'tiramisu-tradizionale',
            'base_price' => 7.50,
            'is_available' => true,
        ]);

        // Customer sees original price
        $response1 = $this->get('/menu');
        $response1->assertSee('7.50');

        // Admin updates price to 9.00
        $this->actingAs($this->admin)->put("/admin/products/{$product->id}", [
            'category_id' => $category->id,
            'name' => 'Tiramisu Tradizionale',
            'slug' => 'tiramisu-tradizionale',
            'base_price' => 9.00,
            'is_available' => 1,
            'has_variants' => 0,
        ]);

        // Customer sees updated price immediately
        $response2 = $this->get('/menu');
        $response2->assertSee('9.00');
    }
}
