<?php

namespace Database\Factories;

use App\Enums\RecordStatus;
use App\Models\AuthorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuthorCategory>
 */
class AuthorCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->jobTitle(),
            'status' => RecordStatus::Active,
        ];
    }
}
