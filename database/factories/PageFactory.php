<?php

namespace Database\Factories;

use App\Enums\PageTemplate;
use App\Enums\PublishStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    public function definition(): array
    {
        $title = ucwords(fake()->unique()->words(3, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => '<p>'.fake()->paragraph().'</p>',
            'status' => PublishStatus::Published,
            'template' => PageTemplate::Layout,
        ];
    }
}
