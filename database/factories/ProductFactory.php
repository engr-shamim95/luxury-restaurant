<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        return [
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(10, 9999),
            'description' => fake()->paragraph(),
            'base_price' => fake()->randomFloat(2, 5, 50),
            'image' => null,
            'is_available' => true,
            'has_variants' => false,
        ];
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }

    public function withVariants(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_variants' => true,
        ]);
    }
}
