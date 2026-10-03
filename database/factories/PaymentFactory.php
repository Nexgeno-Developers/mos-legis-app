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
            // payable_id first: user_id is read from the submission once it exists.
            'payable_type' => 'manuscript_submissions',
            'payable_id' => ManuscriptSubmission::factory(),
            'user_id' => fn (array $attributes) => ManuscriptSubmission::find($attributes['payable_id'])?->user_id,
            'payment_purpose' => PaymentPurpose::Prescreening,
            // Tax-inclusive ₹150: taxable value + 18% GST.
            'amount' => 127.12,
            'tax_amount' => 22.88,
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
