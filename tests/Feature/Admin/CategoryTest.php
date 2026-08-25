<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_categories_list(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Menu Categories');
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Artisanal Calzones',
            'slug' => 'artisanal-calzones',
            'description' => 'Stuffed folded pizzas baked to golden perfection.',
            'order' => 5,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Artisanal Calzones',
            'slug' => 'artisanal-calzones',
            'order' => 5,
            'is_active' => 1,
        ]);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Old Category']);

        $response = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Updated Category',
            'slug' => $category->slug,
            'description' => 'New description',
            'order' => 2,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
        ]);
    }

    public function test_admin_can_delete_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }
}
