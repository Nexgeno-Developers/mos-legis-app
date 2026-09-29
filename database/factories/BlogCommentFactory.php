<?php

namespace Database\Factories;

use App\Enums\CommentStatus;
use App\Models\Blog;
use App\Models\BlogComment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlogComment>
 */
class BlogCommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'blog_id' => Blog::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'comment' => fake()->sentences(2, true),
            'status' => CommentStatus::Pending,
        ];
    }
}
