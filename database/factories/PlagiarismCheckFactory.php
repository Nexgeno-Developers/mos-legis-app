<?php

namespace Database\Factories;

use App\Enums\PlagiarismCheckStatus;
use App\Enums\PlagiarismCheckType;
use App\Models\PlagiarismCheck;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlagiarismCheck>
 */
class PlagiarismCheckFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'check_type' => PlagiarismCheckType::Standalone,
            'title' => fake()->sentence(5),
            'content' => fake()->paragraphs(3, true),
            'check_status' => PlagiarismCheckStatus::Pending,
        ];
    }
}
