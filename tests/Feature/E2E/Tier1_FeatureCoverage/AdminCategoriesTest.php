<?php

namespace Tests\Feature\E2E\Tier1_FeatureCoverage;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCategoriesTest extends TestCase
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

    public function test_admin_can_view_categories_index(): void
    {
        Category::create([
            'name' => 'Appetizers & Starters',
            'slug' => 'appetizers-starters',
            'description' => 'Delicious openers to your meal.',
            'is_active' => true,
            'order' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/categories');

        $response->assertStatus(200);
        $response->assertSee('Appetizers &amp; Starters', false);
    }

    public function test_admin_can_create_category(): void
    {
        $payload = [
            'name' => 'Wood-Fired Pizzas',
            'slug' => 'wood-fired-pizzas',
            'description' => 'Traditional artisan pizzas baked in wood-fired oven.',
            'is_active' => 1,
            'order' => 2,
        ];

        $response = $this->actingAs($this->adminUser)->post('/admin/categories', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'Wood-Fired Pizzas',
            'slug' => 'wood-fired-pizzas',
            'is_active' => true,
            'order' => 2,
        ]);
    }

    public function test_admin_can_view_category_edit_form(): void
    {
        $category = Category::create([
            'name' => 'Handmade Pastas',
            'slug' => 'handmade-pastas',
            'description' => 'Fresh pasta made daily.',
            'is_active' => true,
            'order' => 3,
        ]);

        $response = $this->actingAs($this->adminUser)->get("/admin/categories/{$category->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Handmade Pastas');
        $response->assertSee('handmade-pastas');
    }

    public function test_admin_can_update_category_details(): void
    {
        $category = Category::create([
            'name' => 'Beverages',
            'slug' => 'beverages',
            'description' => 'Drinks menu',
            'is_active' => true,
            'order' => 4,
        ]);

        $response = $this->actingAs($this->adminUser)->put("/admin/categories/{$category->id}", [
            'name' => 'Artisan Beverages & Cocktails',
            'slug' => 'beverages-cocktails',
            'description' => 'Craft mocktails, beers, and fine wines.',
            'is_active' => 1,
            'order' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Artisan Beverages & Cocktails',
            'slug' => 'beverages-cocktails',
            'order' => 5,
        ]);
    }

    public function test_admin_can_toggle_category_active_status(): void
    {
        $category = Category::create([
            'name' => 'Seasonal Fall Menu',
            'slug' => 'seasonal-fall-menu',
            'is_active' => true,
            'order' => 10,
        ]);

        $response = $this->actingAs($this->adminUser)->put("/admin/categories/{$category->id}", [
            'name' => 'Seasonal Fall Menu',
            'slug' => 'seasonal-fall-menu',
            'is_active' => 0,
            'order' => 10,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_category(): void
    {
        $category = Category::create([
            'name' => 'Old Promo Items',
            'slug' => 'old-promo-items',
            'is_active' => false,
            'order' => 99,
        ]);

        $response = $this->actingAs($this->adminUser)->delete("/admin/categories/{$category->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
