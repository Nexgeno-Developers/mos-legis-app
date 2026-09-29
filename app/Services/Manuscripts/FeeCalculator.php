<?php

namespace App\Services\Manuscripts;

use App\Models\ManuscriptFee;
use App\Models\ManuscriptSubmission;

/**
 * Fee and tax rules: pre-screening fee from Settings (SOW A.16/A.21), publication
 * fee from the Author × Content matrix (A.15), tax only for Indian billing
 * addresses (clarification #5). Single currency (INR) per clarification.
 */
class FeeCalculator
{
    public function prescreeningFee(): float
    {
        return round(settings()->float('manuscript.plagiarism_prescreening_fee'), 2);
    }

    /** Assumption: the standalone plagiarism check costs the same as the pre-screening fee. */
    public function standaloneCheckFee(): float
    {
        return $this->prescreeningFee();
    }

    /** Null when the combination is not offered (blank cell in the fee matrix). */
    public function publicationFee(int $authorCategoryId, int $contentCategoryId): ?float
    {
        $fee = ManuscriptFee::query()
            ->where('author_category_id', $authorCategoryId)
            ->where('content_category_id', $contentCategoryId)
            ->value('fees');

        return $fee === null ? null : round((float) $fee, 2);
    }

    public function publicationFeeFor(ManuscriptSubmission $submission): ?float
    {
        return $this->publicationFee($submission->author_category_id, $submission->content_category_id);
    }

    /** @return array{rate: float, tax: float, total: float} */
    public function withTax(float $amount, ?string $countryCode): array
    {
        $rate = strtoupper((string) $countryCode) === 'IN' ? settings()->float('payment.tax_rate_percent') : 0.0;
        $tax = round($amount * $rate / 100, 2);

        return ['rate' => $rate, 'tax' => $tax, 'total' => round($amount + $tax, 2)];
    }

    public function paymentsEnabled(): bool
    {
        return settings()->bool('payment.payment_enabled');
    }
}
