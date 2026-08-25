<?php

namespace Database\Factories;

use App\Models\NavigationItem;
use App\Models\NavigationMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NavigationItem>
 */
class NavigationItemFactory extends Factory
{
    protected $model = NavigationItem::class;

    public function definition(): array
    {
        return [
            'navigation_menu_id' => NavigationMenu::factory(),
            'label' => fake()->word(),
            'url' => '/' . fake()->slug(),
            'page_id' => null,
            'order' => fake()->numberBetween(0, 10),
            'target' => '_self',
        ];
    }
}
