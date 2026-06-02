<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use App\Services\ApiPayloadCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SeoSettingController extends Controller
{
    /**
     * Public robots.txt content API for frontend file generation.
     *
     * - JSON: GET /api/v1/robots-txt
     * - Plain text: GET /api/v1/robots-txt?format=txt (or Accept: text/plain)
     */
    public function robotsTxt(Request $request): Response|JsonResponse
    {
        $companyId = $this->companyIdFromConfig();
        $wantsText = $this->wantsPlainText($request);

        $cached = ApiPayloadCache::getCachedRobotsTxtPayload($companyId);
        if ($cached !== null) {
            return $wantsText
                ? $this->plainTextResponse((string) ($cached['content'] ?? ''))
                : response()->json(['data' => $cached]);
        }

        $setting = SeoSetting::query()->where('company_id', $companyId)->first();
        if ($setting === null && $companyId !== 0) {
            // Fallback to global/default (company_id=0) if a per-company row is not present.
            $setting = SeoSetting::query()->where('company_id', 0)->first();
        }

        $content = $this->normalizeRobotsTxt($setting?->content);

        $payload = [
            'generated_at' => now()->toISOString(),
            'company_id' => $companyId,
            'source_company_id' => $setting?->company_id,
            'updated_at' => $setting?->updated_at?->toISOString(),
            'content' => $content,
            'content_hash' => hash('sha256', $content),
        ];

        ApiPayloadCache::storeRobotsTxtPayload($companyId, $payload);

        return $wantsText
            ? $this->plainTextResponse($content)
            : response()->json(['data' => $payload]);
    }

    private function companyIdFromConfig(): int
    {
        $raw = config('custom.company_id');
        if (!is_numeric($raw) || (int) $raw <= 0) {
            return 0;
        }

        return (int) $raw;
    }

    private function wantsPlainText(Request $request): bool
    {
        $format = strtolower((string) $request->query('format', ''));
        if ($format === 'txt' || $format === 'text') {
            return true;
        }

        $accept = strtolower((string) $request->header('Accept', ''));

        return str_contains($accept, 'text/plain');
    }

    private function plainTextResponse(string $content): Response
    {
        return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    private function normalizeRobotsTxt(?string $raw): string
    {
        if (!filled($raw)) {
            return "User-agent: *\nDisallow:\n";
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $raw);
        $normalized = rtrim($normalized, "\n") . "\n";

        return $normalized;
    }
}
