<?php

namespace Tests\Feature\E2E\Tier2_BoundaryAndCornerCases;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminBoundaryTest extends TestCase
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

    public function test_category_creation_fails_without_required_name(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/categories', [
            'name' => '',
            'slug' => 'no-name-category',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseMissing('categories', ['slug' => 'no-name-category']);
    }

    public function test_product_creation_fails_without_category_id_or_price(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/products', [
            'name' => 'Incomplete Product',
            'slug' => 'incomplete-product',
            'base_price' => '',
            'category_id' => '',
        ]);

        $response->assertSessionHasErrors(['category_id', 'base_price']);
    }

    public function test_page_creation_fails_without_title(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/pages', [
            'title' => '',
            'slug' => 'titleless-page',
            'content' => 'Sample content',
        ]);

        $response->assertSessionHasErrors(['title']);
    }

    public function test_duplicate_page_slug_fails_validation(): void
    {
        Page::create([
            'title' => 'First Page',
            'slug' => 'unique-slug',
            'content' => 'First content',
        ]);

        $response = $this->actingAs($this->adminUser)->post('/admin/pages', [
            'title' => 'Second Page',
            'slug' => 'unique-slug',
            'content' => 'Second content',
        ]);

        $response->assertSessionHasErrors(['slug']);
    }

    public function test_category_with_excessively_long_name_fails_validation(): void
    {
        $longName = str_repeat('A', 256);

        $response = $this->actingAs($this->adminUser)->post('/admin/categories', [
            'name' => $longName,
            'slug' => 'long-name-category',
        ]);

        $response->assertSessionHasErrors(['name']);
    }
}
