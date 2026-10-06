<?php

namespace App\Services\Manuscripts;

use App\Models\ManuscriptCoAuthorFee;
use App\Models\ManuscriptFee;
use App\Models\ManuscriptSubmission;

/**
 * Fee and tax rules: pre-screening fee from Settings (SOW A.16/A.21), publication
 * fee from the Author × Content matrix (A.15), tax only for Indian billing
 * addresses (clarification #5). Single currency (INR) per clarification.
 * A co-author surcharge per content category is added to the publication fee.
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

    /** Publication fee for a submission: matrix fee + co-author surcharge. Null when the combination is not offered. */
    public function publicationFeeFor(ManuscriptSubmission $submission): ?float
    {
        $breakdown = $this->publicationBreakdown($submission);

        return $breakdown === null ? null : $breakdown['total'];
    }

    /**
     * @return array{base: float, co_authors: int, first_two_fee: float, additional_fee: float, surcharge: float, total: float}|null
     */
    public function publicationBreakdown(ManuscriptSubmission $submission): ?array
    {
        $base = $this->publicationFee($submission->author_category_id, $submission->content_category_id);

        if ($base === null) {
            return null;
        }

        $count = self::coAuthorCount($submission->co_authors);
        [$firstTwo, $additional] = $this->coAuthorRates($submission->content_category_id);
        $surcharge = self::surcharge($count, $firstTwo, $additional);

        return [
            'base' => $base,
            'co_authors' => $count,
            'first_two_fee' => $firstTwo,
            'additional_fee' => $additional,
            'surcharge' => $surcharge,
            'total' => round($base + $surcharge, 2),
        ];
    }

    /** @return array{0: float, 1: float} [fee for each of the 1st/2nd co-authors, fee for each from the 3rd on] */
    public function coAuthorRates(int $contentCategoryId): array
    {
        $row = ManuscriptCoAuthorFee::where('content_category_id', $contentCategoryId)->first();

        return [round((float) $row?->first_two_fee, 2), round((float) $row?->additional_fee, 2)];
    }

    public function coAuthorSurcharge(int $contentCategoryId, int $coAuthors): float
    {
        return self::surcharge($coAuthors, ...$this->coAuthorRates($contentCategoryId));
    }

    /** 1st and 2nd co-authors pay $firstTwo each, every further co-author pays $additional. */
    public static function surcharge(int $coAuthors, float $firstTwo, float $additional): float
    {
        $coAuthors = max(0, $coAuthors);

        return round(min($coAuthors, 2) * $firstTwo + max($coAuthors - 2, 0) * $additional, 2);
    }

    /** Named co-authors only (blank entries are ignored). */
    public static function coAuthorCount(?array $coAuthors): int
    {
        return collect($coAuthors ?? [])->filter(fn ($name) => filled($name))->count();
    }

    /**
     * Fees are inclusive of all taxes: the payer is charged exactly $amount. For Indian billing
     * addresses the tax is carved out of it (taxable value + tax = fee); others are zero-rated.
     *
     * @return array{rate: float, base: float, tax: float, total: float}
     */
    public function withTax(float $amount, ?string $countryCode): array
    {
        $rate = strtoupper((string) $countryCode) === 'IN' ? settings()->float('payment.tax_rate_percent') : 0.0;
        $tax = $rate > 0 ? round($amount * $rate / (100 + $rate), 2) : 0.0;

        return ['rate' => $rate, 'base' => round($amount - $tax, 2), 'tax' => $tax, 'total' => round($amount, 2)];
    }

    public function paymentsEnabled(): bool
    {
        return settings()->bool('payment.payment_enabled');
    }
}
