<?php

namespace App\Services\Plagiarism;

/**
 * Third-party plagiarism checker (SOW A.18). Implement this for the chosen vendor
 * and register it in App\Providers\AppServiceProvider under PLAGIARISM_DRIVER.
 */
interface PlagiarismChecker
{
    public function check(string $title, string $text): PlagiarismResult;
}
