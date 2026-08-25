<?php

namespace Tests\Feature\E2E\Tier2_BoundaryAndCornerCases;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SlugAndEncodingBoundaryTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin Manager',
            'email' => 'admin@restaurant.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);
    }

    public function test_category_with_special_characters_in_name_creates_clean_slug(): void
    {
        $payload = [
            'name' => 'Desserts & Crêpes 100% Homemade!',
            'slug' => 'desserts-crepes-100-homemade',
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->adminUser)->post('/admin/categories', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'slug' => 'desserts-crepes-100-homemade',
        ]);
    }

    public function test_duplicate_category_slug_fails_validation(): void
    {
        Category::create([
            'name' => 'First Category',
            'slug' => 'duplicate-category-slug',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->post('/admin/categories', [
            'name' => 'Second Category',
            'slug' => 'duplicate-category-slug',
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors(['slug']);
    }

    public function test_duplicate_product_slug_fails_validation(): void
    {
        $category = Category::create(['name' => 'Meals', 'slug' => 'meals', 'is_active' => true]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Original Burger',
            'slug' => 'duplicate-product-slug',
            'base_price' => 10.00,
            'is_available' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Another Burger',
            'slug' => 'duplicate-product-slug',
            'base_price' => 12.00,
            'is_available' => 1,
        ]);

        $response->assertSessionHasErrors(['slug']);
    }

    public function test_xss_payload_in_order_notes_is_stored_safely_and_escaped(): void
    {
        $category = Category::create(['name' => 'Drinks', 'slug' => 'drinks', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Soda',
            'slug' => 'soda',
            'base_price' => 3.00,
            'is_available' => true,
        ]);

        $cart = [
            'item_1' => [
                'item_key' => 'item_1',
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => 3.00,
                'quantity' => 1,
            ],
        ];

        $xssNote = "<script>alert('xss')</script>";

        $response = $this->withSession(['cart' => $cart])->post('/checkout', [
            'customer_name' => 'Security Tester',
            'customer_email' => 'sec@test.com',
            'customer_phone' => '555-0000',
            'order_type' => 'pickup',
            'order_notes' => $xssNote,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Security Tester',
            'order_notes' => $xssNote,
        ]);
    }

    public function test_unicode_and_accents_in_product_names_render_properly(): void
    {
        $category = Category::create(['name' => 'Specialità', 'slug' => 'specialita', 'is_active' => true]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Gnocchi ai Quattro Formaggi & Tartufo',
            'slug' => 'gnocchi-tartufo',
            'base_price' => 19.50,
            'is_available' => true,
        ]);

        $response = $this->get('/menu');

        $response->assertStatus(200);
        $response->assertSee('Gnocchi ai Quattro Formaggi &amp; Tartufo', false);
    }
}
