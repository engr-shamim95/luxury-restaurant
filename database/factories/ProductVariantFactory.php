<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory()->withVariants(),
            'name' => fake()->randomElement(['Small (10")', 'Medium (12")', 'Large (16")', 'Extra Cheese', 'Gluten Free']),
            'type' => fake()->randomElement(['size', 'addon', 'option']),
            'price_adjustment' => fake()->randomFloat(2, 0, 8),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
