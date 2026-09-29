<?php

namespace Database\Factories;

use App\Enums\EnquiryForm;
use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enquiry>
 */
class EnquiryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'form_name' => EnquiryForm::Contact,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => null,
            'ip' => fake()->ipv4(),
            'form_data' => ['purpose' => 'General query', 'submission_id' => null, 'message' => fake()->paragraph()],
        ];
    }
}
