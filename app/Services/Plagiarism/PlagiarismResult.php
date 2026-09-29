<?php

namespace App\Services\Plagiarism;

final readonly class PlagiarismResult
{
    /**
     * @param  list<array{source: string, url: ?string, similarity: float}>  $matches
     * @param  array<string, mixed>  $raw  full vendor response, stored in plagiarism_checks.api_response
     */
    public function __construct(
        public float $similarity,
        public array $matches = [],
        public array $raw = [],
    ) {}
}
