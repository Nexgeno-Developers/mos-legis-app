<?php

namespace Database\Factories;

use App\Enums\RecordStatus;
use App\Models\ContentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentCategory>
 */
class ContentCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true).' Articles',
            'min_word_limit' => 100,
            'max_word_limit' => 10000,
            'guideline' => fake()->sentence(),
            'status' => RecordStatus::Active,
        ];
    }
}
