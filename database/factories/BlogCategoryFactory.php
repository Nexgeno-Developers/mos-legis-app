<?php

namespace Database\Factories;

use App\Enums\RecordStatus;
use App\Models\BlogCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogCategory>
 */
class BlogCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = ucwords(fake()->unique()->words(2, true));

        return [
            'category_name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'status' => RecordStatus::Active,
        ];
    }
}
