<?php

namespace Tests\Feature\E2E\Tier1_FeatureCoverage;

use App\Models\NavigationItem;
use App\Models\NavigationMenu;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
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

    public function test_admin_can_view_navigation_manager(): void
    {
        $headerMenu = NavigationMenu::create(['name' => 'Main Header Menu', 'location' => 'header']);
        NavigationItem::create([
            'navigation_menu_id' => $headerMenu->id,
            'label' => 'Home',
            'url' => '/',
            'order' => 1,
            'target' => '_self',
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/navigation');

        $response->assertStatus(200);
        $response->assertSee('Main Header Menu');
        $response->assertSee('Home');
    }

    public function test_admin_can_create_navigation_menu(): void
    {
        $payload = [
            'name' => 'Sidebar Quick Links',
            'location' => 'sidebar',
        ];

        $response = $this->actingAs($this->adminUser)->post('/admin/navigation/menus', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('navigation_menus', [
            'name' => 'Sidebar Quick Links',
            'location' => 'sidebar',
        ]);
    }

    public function test_admin_can_add_custom_url_item_to_navigation_menu(): void
    {
        $menu = NavigationMenu::create(['name' => 'Header Menu', 'location' => 'header']);

        $payload = [
            'navigation_menu_id' => $menu->id,
            'label' => 'Online Reservations',
            'url' => 'https://opentable.com/restaurant-booking',
            'order' => 2,
            'target' => '_blank',
        ];

        $response = $this->actingAs($this->adminUser)->post('/admin/navigation/items', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('navigation_items', [
            'navigation_menu_id' => $menu->id,
            'label' => 'Online Reservations',
            'url' => 'https://opentable.com/restaurant-booking',
            'target' => '_blank',
        ]);
    }

    public function test_admin_can_add_navigation_item_linked_to_page(): void
    {
        $menu = NavigationMenu::create(['name' => 'Footer Menu', 'location' => 'footer']);
        $page = Page::create([
            'title' => 'Our Chef Story',
            'slug' => 'chef-story',
            'content' => 'Story about chef.',
            'is_published' => true,
        ]);

        $payload = [
            'navigation_menu_id' => $menu->id,
            'label' => 'Our Chef',
            'page_id' => $page->id,
            'order' => 1,
            'target' => '_self',
        ];

        $response = $this->actingAs($this->adminUser)->post('/admin/navigation/items', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('navigation_items', [
            'navigation_menu_id' => $menu->id,
            'label' => 'Our Chef',
            'page_id' => $page->id,
        ]);
    }

    public function test_admin_can_update_navigation_item(): void
    {
        $menu = NavigationMenu::create(['name' => 'Header Menu', 'location' => 'header']);
        $item = NavigationItem::create([
            'navigation_menu_id' => $menu->id,
            'label' => 'Old Link',
            'url' => '/old-link',
            'order' => 1,
            'target' => '_self',
        ]);

        $response = $this->actingAs($this->adminUser)->put("/admin/navigation/items/{$item->id}", [
            'navigation_menu_id' => $menu->id,
            'label' => 'Updated Menu Label',
            'url' => '/menu',
            'order' => 3,
            'target' => '_self',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('navigation_items', [
            'id' => $item->id,
            'label' => 'Updated Menu Label',
            'url' => '/menu',
            'order' => 3,
        ]);
    }

    public function test_admin_can_delete_navigation_item(): void
    {
        $menu = NavigationMenu::create(['name' => 'Header Menu', 'location' => 'header']);
        $item = NavigationItem::create([
            'navigation_menu_id' => $menu->id,
            'label' => 'Obsolete Item',
            'url' => '/obsolete',
            'order' => 99,
        ]);

        $response = $this->actingAs($this->adminUser)->delete("/admin/navigation/items/{$item->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('navigation_items', ['id' => $item->id]);
    }
}
