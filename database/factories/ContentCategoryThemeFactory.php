<?php

namespace Database\Factories;

use App\Models\ContentCategory;
use App\Models\ContentCategoryTheme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentCategoryTheme>
 */
class ContentCategoryThemeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content_category_id' => ContentCategory::factory(),
            'name' => fake()->sentence(4),
            'volume' => fake()->numberBetween(1, 9),
            'period' => now()->startOfMonth(),
        ];
    }
}
