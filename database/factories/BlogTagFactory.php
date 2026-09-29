<?php

namespace Database\Factories;

use App\Enums\RecordStatus;
use App\Models\BlogTag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogTag>
 */
class BlogTagFactory extends Factory
{
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word());

        return [
            'tag_name' => $name,
            'slug' => Str::slug($name),
            'status' => RecordStatus::Active,
        ];
    }
}
