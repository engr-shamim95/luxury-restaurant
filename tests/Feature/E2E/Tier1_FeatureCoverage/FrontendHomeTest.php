<?php

namespace Tests\Feature\E2E\Tier1_FeatureCoverage;

use App\Models\Category;
use App\Models\NavigationItem;
use App\Models\NavigationMenu;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_returns_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_homepage_displays_dynamic_restaurant_name_from_settings(): void
    {
        Setting::create([
            'key' => 'restaurant_name',
            'value' => 'Luigi Artisan Pizzeria',
            'type' => 'string',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Luigi Artisan Pizzeria');
    }

    public function test_homepage_displays_dynamic_tagline_and_hero_content(): void
    {
        Setting::create(['key' => 'hero_title', 'value' => 'Experience True Neapolitan Flavour', 'type' => 'string']);
        Setting::create(['key' => 'hero_subtitle', 'value' => 'Crafted with passion using certified Italian flour', 'type' => 'string']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Experience True Neapolitan Flavour');
        $response->assertSee('Crafted with passion using certified Italian flour');
    }

    public function test_homepage_renders_active_categories(): void
    {
        Category::create([
            'name' => 'Antipasti Specials',
            'slug' => 'antipasti-specials',
            'description' => 'Fine cured meats and cheeses.',
            'is_active' => true,
            'order' => 1,
        ]);
        Category::create([
            'name' => 'Gourmet Calzones',
            'slug' => 'gourmet-calzones',
            'description' => 'Stuffed folded pizzas.',
            'is_active' => true,
            'order' => 2,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Antipasti Specials');
        $response->assertSee('Gourmet Calzones');
    }

    public function test_homepage_renders_header_navigation_links(): void
    {
        $headerMenu = NavigationMenu::create(['name' => 'Header Menu', 'location' => 'header']);
        NavigationItem::create([
            'navigation_menu_id' => $headerMenu->id,
            'label' => 'Our Full Menu',
            'url' => '/menu',
            'order' => 1,
            'target' => '_self',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Our Full Menu');
    }

    public function test_homepage_displays_operating_hours_in_footer(): void
    {
        Setting::create([
            'key' => 'opening_hours',
            'value' => 'Everyday 11:30 AM - 11:00 PM',
            'type' => 'string',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Everyday 11:30 AM - 11:00 PM');
    }
}
