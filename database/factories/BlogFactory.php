<?php

namespace Database\Factories;

use App\Enums\BlogStatus;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    public function definition(): array
    {
        $title = rtrim(fake()->sentence(6), '.');

        return [
            'user_id' => User::factory(),
            'blog_title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
            'category_id' => BlogCategory::factory(),
            'author_name' => fake()->name(),
            'excerpt' => fake()->sentence(18),
            'content' => '<p>'.implode('</p><p>', fake()->paragraphs(5)).'</p>',
            'status' => BlogStatus::Published,
            'publish_date' => today()->subDays(fake()->numberBetween(1, 90)),
            'featured_post' => false,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => BlogStatus::Draft]);
    }
}
