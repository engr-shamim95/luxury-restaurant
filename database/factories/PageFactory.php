<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);
        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(100, 9999),
            'content' => fake()->paragraphs(3, true),
            'meta_title' => $title,
            'meta_description' => fake()->sentence(10),
            'og_image' => null,
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
}
