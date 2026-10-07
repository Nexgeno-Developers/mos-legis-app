<?php

namespace App\Services\Plagiarism;

use RuntimeException;
use Throwable;

/**
 * The plagiarism vendor could not check the content. Retryable failures (rate limit, outage, timeout)
 * let the queued job try again; the others (bad API key, no credits, no text) fail the check at once.
 */
class PlagiarismCheckFailed extends RuntimeException
{
    public function __construct(string $message, public readonly bool $retryable = true, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
