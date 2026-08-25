<?php

namespace Database\Factories;

use App\Models\NavigationMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NavigationMenu>
 */
class NavigationMenuFactory extends Factory
{
    protected $model = NavigationMenu::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'location' => fake()->unique()->slug(),
        ];
    }
}
