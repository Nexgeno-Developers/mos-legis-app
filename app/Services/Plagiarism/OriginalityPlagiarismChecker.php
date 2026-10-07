<?php

namespace App\Services\Plagiarism;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Originality.ai plagiarism checker (API v3, https://docs.originality.ai).
 *
 * - POST /scan with only check_plagiarism on (AI, facts, readability and grammar are off, so only
 *   plagiarism credits are used). Every request carries the X-OAI-API-KEY header.
 * - A plagiarism scan can take up to 60 s, so long manuscripts are scanned in chunks, as the docs
 *   advise, and the scores are combined weighted by words.
 * - Finished chunks are cached for a day, so a retried job never pays for the same text twice.
 * - 429 (limit: 500 requests/minute), 5xx and network errors are retried; 401/403 etc. fail the check.
 */
class OriginalityPlagiarismChecker implements PlagiarismChecker
{
    public const DEFAULT_URL = 'https://api.originality.ai/api/v3';

    /** @param  array<string, mixed>  $config  config('services.plagiarism') */
    public function __construct(private readonly array $config) {}

    public function check(string $title, string $text): PlagiarismResult
    {
        $chunks = self::chunks($text, max(200, (int) ($this->config['chunk_words'] ?? 1500)));

        if ($chunks === []) {
            throw new PlagiarismCheckFailed('No text could be read from the content to check.', retryable: false);
        }

        $total = count($chunks);
        $scans = [];
        foreach ($chunks as $i => $chunk) {
            $partTitle = $total > 1 ? Str::limit($title, 180).' (part '.($i + 1).'/'.$total.')' : Str::limit($title, 200);
            $scans[] = ['words' => self::wordCount($chunk), 'response' => $this->scan($partTitle, $chunk)];
        }

        return self::combine($scans);
    }

    /** Remaining credits, e.g. ['credits' => 8646, 'subscriptionCredits' => 1745]. */
    public function balance(): array
    {
        return (array) $this->send(fn (PendingRequest $http) => $http->timeout(10)->get('account/balance'), retries: 1)->json();
    }

    /** One plagiarism scan; the response is cached by content so a retry is free. */
    private function scan(string $title, string $content): array
    {
        $key = 'originality-scan:'.sha1($content);

        if (is_array($cached = Cache::get($key))) {
            return $cached;
        }

        $response = $this->send(fn (PendingRequest $http) => $http->post('scan', [
            'title' => $title,
            'check_ai' => false,
            'check_plagiarism' => true,
            'check_facts' => false,
            'check_readability' => false,
            'check_grammar' => false,
            'check_contentQuality' => false,
            'storeScan' => (bool) ($this->config['store_scan'] ?? true),
            'excludedUrls' => $this->excludedUrls(),
            'aiModelVersion' => $this->config['ai_model'] ?: 'lite',
            'content' => $content,
        ]));

        $json = $response->json();
        if (! is_array($json) || ! isset($json['results']['plagiarism'])) {
            throw new PlagiarismCheckFailed('Originality.ai returned an unexpected response (no plagiarism result).');
        }

        Cache::put($key, $json, now()->addDay());

        return $json;
    }

    /** @param  callable(PendingRequest): Response  $request */
    private function send(callable $request, int $retries = 3): Response
    {
        if (blank($this->config['api_key'] ?? null)) {
            throw new PlagiarismCheckFailed('The Originality.ai API key is not configured (PLAGIARISM_API_KEY).', retryable: false);
        }

        $http = Http::baseUrl(rtrim($this->config['api_url'] ?: self::DEFAULT_URL, '/').'/')
            ->withHeaders(['X-OAI-API-KEY' => $this->config['api_key']])
            ->acceptJson()
            ->asJson()
            ->timeout((int) ($this->config['timeout'] ?? 180))
            ->retry($retries, fn (int $attempt, Throwable $e) => self::backoff($attempt, $e), fn (Throwable $e) => self::isTransient($e));

        try {
            return $request($http)->throw();
        } catch (ConnectionException $e) {
            throw new PlagiarismCheckFailed('Could not reach Originality.ai: '.$e->getMessage(), previous: $e);
        } catch (RequestException $e) {
            $status = $e->response->status();
            $detail = $e->response->json('error') ?? $e->response->json('message') ?? Str::limit(strip_tags($e->response->body()), 200);
            $message = match (true) {
                in_array($status, [401, 403], true) => 'Originality.ai rejected the API key',
                $status === 402 => 'The Originality.ai account has no credits left',
                $status === 429 => 'Originality.ai rate limit reached',
                default => "Originality.ai error {$status}",
            };

            throw new PlagiarismCheckFailed(
                $message.(is_string($detail) && $detail !== '' ? ': '.$detail : '').'.',
                retryable: $status === 429 || $status >= 500,
                previous: $e,
            );
        }
    }

    private static function isTransient(Throwable $e): bool
    {
        return $e instanceof ConnectionException
            || ($e instanceof RequestException && ($e->response->status() === 429 || $e->response->status() >= 500));
    }

    /** Milliseconds to wait: Retry-After on a 429, otherwise 2 s, 4 s, 8 s. */
    private static function backoff(int $attempt, Throwable $e): int
    {
        $retryAfter = $e instanceof RequestException ? (int) $e->response->header('Retry-After') : 0;

        return $retryAfter > 0 ? min($retryAfter, 60) * 1000 : 1000 * 2 ** $attempt;
    }

    /** The journal's own site (its published articles) plus any configured URLs. */
    private function excludedUrls(): array
    {
        $urls = array_filter(array_map('trim', explode(',', (string) ($this->config['excluded_urls'] ?? ''))));
        $site = rtrim((string) config('app.url'), '/');

        if ($site !== '' && ! preg_match('#^https?://(localhost|127\.0\.0\.1|\[::1\])(:\d+)?$#', $site)) {
            $urls[] = $site;
        }

        return array_values(array_unique($urls));
    }

    /**
     * Split text into chunks of at most $size words, breaking at paragraph or sentence ends where
     * possible. A short tail is merged into the previous chunk.
     *
     * @return list<string>
     */
    public static function chunks(string $text, int $size): array
    {
        $text = trim((string) preg_replace('/[ \t]+/u', ' ', str_replace("\r", '', $text)));
        if ($text === '') {
            return [];
        }

        $pieces = [];
        foreach (preg_split('/\n+/u', $text) as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                continue;
            }
            if (self::wordCount($paragraph) <= $size) {
                $pieces[] = $paragraph;

                continue;
            }
            // A paragraph longer than a chunk: split by sentences, and a huge sentence by words.
            foreach (preg_split('/(?<=[.!?])\s+/u', $paragraph) as $sentence) {
                foreach (array_chunk(preg_split('/\s+/u', trim($sentence), -1, PREG_SPLIT_NO_EMPTY), $size) as $words) {
                    $pieces[] = implode(' ', $words);
                }
            }
        }

        $chunks = [];
        $current = '';
        $count = 0;
        foreach ($pieces as $piece) {
            $words = self::wordCount($piece);
            if ($count > 0 && $count + $words > $size) {
                $chunks[] = $current;
                $current = '';
                $count = 0;
            }
            $current .= ($current === '' ? '' : "\n\n").$piece;
            $count += $words;
        }

        if ($current !== '') {
            if ($chunks !== [] && $count < min(100, intdiv($size, 4))) {
                $chunks[count($chunks) - 1] .= "\n\n".$current;
            } else {
                $chunks[] = $current;
            }
        }

        return $chunks;
    }

    private static function wordCount(string $text): int
    {
        return count(preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * Overall similarity = word-weighted average of the chunk scores (plagiarism.score, 0–100).
     * Each source's share = words of the phrases it matched ÷ all words checked.
     *
     * @param  list<array{words: int, response: array}>  $scans
     */
    public static function combine(array $scans): PlagiarismResult
    {
        $totalWords = max(1, array_sum(array_column($scans, 'words')));
        $weighted = 0.0;
        $credits = 0;
        $sources = [];
        $phrases = [];
        $meta = [];

        foreach ($scans as $scan) {
            $results = $scan['response']['results'] ?? [];
            $plagiarism = $results['plagiarism'] ?? [];
            $score = min(100.0, max(0.0, (float) ($plagiarism['score'] ?? 0)));
            $weighted += $score * $scan['words'];
            $credits += (int) ($results['credits']['used'] ?? 0);
            $meta[] = array_filter([
                'scan_id' => $results['properties']['id'] ?? null,
                'private_id' => $results['properties']['privateID'] ?? null,
                'public_link' => $results['properties']['publicLink'] ?? null,
                'words' => $scan['words'],
                'score' => $score,
                'credits_used' => $results['credits']['used'] ?? null,
            ], fn ($value) => $value !== null);

            foreach ((array) ($plagiarism['results'] ?? []) as $match) {
                $phrase = (string) ($match['phrase'] ?? '');
                $phraseWords = self::wordCount($phrase);
                $seen = [];

                foreach ((array) ($match['results'] ?? []) as $source) {
                    $url = $source['link'] ?? null;
                    $key = $url ?: ($source['title'] ?? 'Unknown source');
                    if (isset($seen[$key])) {
                        continue; // a phrase counts once per source
                    }
                    $seen[$key] = true;
                    $sources[$key] ??= [
                        'source' => ($source['title'] ?? null) ?: (parse_url((string) $url, PHP_URL_HOST) ?: 'Unknown source'),
                        'url' => $url,
                        'words' => 0,
                    ];
                    $sources[$key]['words'] += $phraseWords;
                }

                if ($phrase !== '' && count($phrases) < 50) {
                    $phrases[] = ['phrase' => Str::limit($phrase, 300), 'sources' => array_slice(array_keys($seen), 0, 5)];
                }
            }
        }

        $matches = collect($sources)
            ->map(fn (array $s) => ['source' => $s['source'], 'url' => $s['url'], 'similarity' => round(min(100, $s['words'] / $totalWords * 100), 2)])
            ->sortByDesc('similarity')
            ->take(20)
            ->values()
            ->all();

        $similarity = round($weighted / $totalWords, 2);

        return new PlagiarismResult($similarity, $matches, [
            'driver' => 'originality',
            'similarity' => $similarity,
            'words_checked' => $totalWords,
            'credits_used' => $credits,
            'scans' => $meta,
            'phrases' => $phrases,
        ]);
    }
}
