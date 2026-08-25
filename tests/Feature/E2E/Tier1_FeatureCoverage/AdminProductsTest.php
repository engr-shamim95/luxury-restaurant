<?php

namespace Tests\Feature\E2E\Tier1_FeatureCoverage;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProductsTest extends TestCase
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
            'name' => 'Main Courses',
            'slug' => 'main-courses',
            'is_active' => true,
            'order' => 1,
        ]);
    }

    public function test_admin_can_view_products_index(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'name' => 'Margherita Pizza',
            'slug' => 'margherita-pizza',
            'description' => 'San Marzano tomatoes, fresh mozzarella, basil.',
            'base_price' => 14.50,
            'is_available' => true,
            'has_variants' => false,
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/products');

        $response->assertStatus(200);
        $response->assertSee('Margherita Pizza');
        $response->assertSee('14.50');
    }

    public function test_admin_can_create_product_with_category(): void
    {
        $payload = [
            'category_id' => $this->category->id,
            'name' => 'Fettuccine Truffle Alfredo',
            'slug' => 'fettuccine-truffle-alfredo',
            'description' => 'Fresh egg fettuccine with black truffle and parmesan.',
            'base_price' => 18.99,
            'is_available' => 1,
            'has_variants' => 0,
        ];

        $response = $this->actingAs($this->adminUser)->post('/admin/products', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'category_id' => $this->category->id,
            'name' => 'Fettuccine Truffle Alfredo',
            'slug' => 'fettuccine-truffle-alfredo',
            'base_price' => 18.99,
            'is_available' => true,
        ]);
    }

    public function test_admin_can_view_product_edit_form(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Lasagna Bolognese',
            'slug' => 'lasagna-bolognese',
            'description' => 'Layered fresh pasta with beef ragu and béchamel.',
            'base_price' => 17.00,
            'is_available' => true,
            'has_variants' => false,
        ]);

        $response = $this->actingAs($this->adminUser)->get("/admin/products/{$product->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Lasagna Bolognese');
        $response->assertSee('17.00');
    }

    public function test_admin_can_update_product_price_and_details(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Eggplant Parmigiana',
            'slug' => 'eggplant-parmigiana',
            'description' => 'Baked eggplant slices.',
            'base_price' => 12.00,
            'is_available' => true,
            'has_variants' => false,
        ]);

        $response = $this->actingAs($this->adminUser)->put("/admin/products/{$product->id}", [
            'category_id' => $this->category->id,
            'name' => 'Eggplant Parmigiana Rustica',
            'slug' => 'eggplant-parmigiana-rustica',
            'description' => 'Crispy baked eggplant with smoked provolone and tomato reduction.',
            'base_price' => 15.50,
            'is_available' => 1,
            'has_variants' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Eggplant Parmigiana Rustica',
            'base_price' => 15.50,
        ]);
    }

    public function test_admin_can_toggle_product_availability(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Fresh Catch Fish',
            'slug' => 'fresh-catch-fish',
            'base_price' => 24.00,
            'is_available' => true,
            'has_variants' => false,
        ]);

        $response = $this->actingAs($this->adminUser)->put("/admin/products/{$product->id}", [
            'category_id' => $this->category->id,
            'name' => 'Fresh Catch Fish',
            'slug' => 'fresh-catch-fish',
            'base_price' => 24.00,
            'is_available' => 0,
            'has_variants' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_available' => false,
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Discontinued Item',
            'slug' => 'discontinued-item',
            'base_price' => 9.99,
            'is_available' => false,
            'has_variants' => false,
        ]);

        $response = $this->actingAs($this->adminUser)->delete("/admin/products/{$product->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
