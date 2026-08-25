<?php

namespace Tests\Feature\Admin;

use App\Models\NavigationItem;
use App\Models\NavigationMenu;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_navigation_manager(): void
    {
        $admin = User::factory()->admin()->create();
        NavigationMenu::factory()->create(['location' => 'header', 'name' => 'Header']);

        $response = $this->actingAs($admin)->get(route('admin.navigation.index'));

        $response->assertStatus(200);
        $response->assertSee('Navigation Menus & Links');
    }

    public function test_admin_can_create_navigation_menu(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.navigation.menus.store'), [
            'name' => 'Sidebar Menu',
            'location' => 'sidebar',
        ]);

        $response->assertRedirect(route('admin.navigation.index'));
        $this->assertDatabaseHas('navigation_menus', [
            'name' => 'Sidebar Menu',
            'location' => 'sidebar',
        ]);
    }

    public function test_admin_can_add_navigation_item_linked_to_page(): void
    {
        $admin = User::factory()->admin()->create();
        $menu = NavigationMenu::factory()->create();
        $page = Page::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.navigation.items.store'), [
            'navigation_menu_id' => $menu->id,
            'label' => 'Our Story',
            'page_id' => $page->id,
            'order' => 1,
            'target' => '_self',
        ]);

        $response->assertRedirect(route('admin.navigation.index'));
        $this->assertDatabaseHas('navigation_items', [
            'navigation_menu_id' => $menu->id,
            'label' => 'Our Story',
            'page_id' => $page->id,
        ]);
    }

    public function test_admin_can_delete_navigation_item(): void
    {
        $admin = User::factory()->admin()->create();
        $item = NavigationItem::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.navigation.items.destroy', $item));

        $response->assertRedirect(route('admin.navigation.index'));
        $this->assertDatabaseMissing('navigation_items', [
            'id' => $item->id,
        ]);
    }
}
