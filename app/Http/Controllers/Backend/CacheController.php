<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CacheController extends Controller
{
    public function clearFrontend(Request $request): JsonResponse
    {
        $url = config('custom.frontend_cache_clear_url');

        if (empty($url)) {
            return response()->json([
                'ok' => false,
                'message' => 'Frontend cache URL is not configured.',
            ], 422);
        }

        try {
            $response = Http::timeout(15)->acceptJson()->get($url);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Failed to reach frontend cache endpoint.',
            ], 502);
        }

        if (!$response->ok()) {
            return response()->json([
                'ok' => false,
                'message' => 'Frontend cache clear failed.',
                'status' => $response->status(),
            ], 502);
        }

        $payload = $response->json();
        if (!is_array($payload)) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid response from frontend cache endpoint.',
            ], 502);
        }

        return response()->json($payload);
    }

    public function generateFrontendSitemap(Request $request): JsonResponse
    {
        $url = config('custom.frontend_sitemap_generate_url');

        if (empty($url)) {
            return response()->json([
                'ok' => false,
                'message' => 'Frontend sitemap generate URL is not configured.',
            ], 422);
        }

        try {
            $response = Http::timeout(30)->acceptJson()->get($url);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Failed to reach frontend sitemap endpoint.',
            ], 502);
        }

        if (!$response->ok()) {
            return response()->json([
                'ok' => false,
                'message' => 'Frontend sitemap generation failed.',
                'status' => $response->status(),
            ], 502);
        }

        $payload = $response->json();
        if (!is_array($payload)) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid response from frontend sitemap endpoint.',
            ], 502);
        }

        return response()->json($payload);
    }

    public function generateFrontendRobots(Request $request): JsonResponse
    {
        $url = config('custom.frontend_robots_generate_url');

        if (empty($url)) {
            return response()->json([
                'ok' => false,
                'message' => 'Frontend robots generate URL is not configured.',
            ], 422);
        }

        try {
            $response = Http::timeout(30)->acceptJson()->get($url);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Failed to reach frontend robots endpoint.',
            ], 502);
        }

        if (!$response->ok()) {
            return response()->json([
                'ok' => false,
                'message' => 'Frontend robots generation failed.',
                'status' => $response->status(),
            ], 502);
        }

        $payload = $response->json();
        if (!is_array($payload)) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid response from frontend robots endpoint.',
            ], 502);
        }

        return response()->json($payload);
    }
}
