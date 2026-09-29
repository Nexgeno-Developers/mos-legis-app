<?php

namespace Database\Factories;

use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Models\ManuscriptSubmission;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => fn (array $attributes) => ManuscriptSubmission::find($attributes['payable_id'])?->user_id,
            'payable_type' => 'manuscript_submissions',
            'payable_id' => ManuscriptSubmission::factory(),
            'payment_purpose' => PaymentPurpose::Prescreening,
            'amount' => 150,
            'tax_amount' => 27,
            'tax_rate' => 18,
            'currency' => 'INR',
            'billing_country_code' => 'IN',
            'payment_status' => PaymentStatus::Pending,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'payment_status' => PaymentStatus::Paid,
            'payment_method' => 'upi',
            'payment_id' => 'pay_'.fake()->bothify('??????????????'),
            'invoice_number' => 'MOS-INV-'.fake()->unique()->numerify('######'),
            'paid_at' => now(),
        ]);
    }
}
