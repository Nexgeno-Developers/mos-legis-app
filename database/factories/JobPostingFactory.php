<?php

namespace Database\Factories;

use App\Enums\ApplicationMethod;
use App\Enums\EmploymentType;
use App\Enums\RecordStatus;
use App\Enums\WorkMode;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobPosting>
 */
class JobPostingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'job_title' => fake()->randomElement(['Associate', 'Research Fellow', 'Legal Intern', 'Senior Associate', 'Law Clerk']),
            'organisation' => fake()->company().' LLP',
            'location' => fake()->randomElement(['Mumbai', 'New Delhi', 'Bengaluru', 'Chennai']),
            'work_mode' => fake()->randomElement(WorkMode::cases()),
            'employment_type' => fake()->randomElement(EmploymentType::cases()),
            'experience' => fake()->randomElement(['0–1 years', '2–4 years', '5+ years']),
            'practice_area' => fake()->randomElement(['Corporate', 'Litigation', 'Arbitration', 'Technology Law', 'Tax']),
            'salary' => '₹'.fake()->numberBetween(4, 20).' LPA',
            'summary' => fake()->paragraph(),
            'responsibilities' => fake()->paragraph(),
            'qualifications' => 'LL.B. from a recognised university.',
            'required_skills' => 'Legal research, drafting, client communication',
            'application_method' => ApplicationMethod::ExternalUrl,
            'application_email_url' => 'https://example.com/careers',
            'application_deadline' => today()->addDays(20),
            'published_date' => today()->subDays(3),
            'expiry_date' => today()->addDays(30),
            'source_name' => 'Firm website',
            'source_url' => 'https://example.com/careers',
            'status' => RecordStatus::Active,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expiry_date' => today()->subDay(), 'application_deadline' => today()->subDays(5)]);
    }
}
