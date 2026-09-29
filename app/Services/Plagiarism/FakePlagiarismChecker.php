<?php

namespace App\Services\Plagiarism;

/**
 * Placeholder until the vendor is chosen. Returns PLAGIARISM_FAKE_SIMILARITY when set,
 * otherwise a deterministic score between 0 and 9.99% derived from the text.
 */
class FakePlagiarismChecker implements PlagiarismChecker
{
    public function __construct(private readonly ?string $fixedSimilarity = null) {}

    public function check(string $title, string $text): PlagiarismResult
    {
        $similarity = $this->fixedSimilarity !== null && $this->fixedSimilarity !== ''
            ? (float) $this->fixedSimilarity
            : round((crc32($title.$text) % 1000) / 100, 2);

        $matches = $similarity > 0 ? [
            ['source' => 'Simulated source — Indian Law Review archive', 'url' => null, 'similarity' => round($similarity * 0.6, 2)],
            ['source' => 'Simulated source — Supreme Court judgment text', 'url' => null, 'similarity' => round($similarity * 0.4, 2)],
        ] : [];

        return new PlagiarismResult($similarity, $matches, [
            'driver' => 'fake',
            'similarity' => $similarity,
            'words_checked' => str_word_count($text),
            'matches' => $matches,
        ]);
    }
}
