<?php

namespace App\Http\Controllers\Backend;

use App\Models\SeoSetting;
use App\Services\ApiPayloadCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class SeoSettingController extends BaseController
{
    protected string $module;

    public function __construct()
    {
        $this->module = 'seo-settings';
        view()->share('module', $this->module);

        $this->middleware('permission:seo-settings view')->only(['index']);
        $this->middleware('permission:seo-settings edit')->only(['update']);
    }

    public function index()
    {
        $companyId = $this->companyIdFromConfig();

        $setting = SeoSetting::query()
            ->where('company_id', $companyId)
            ->first();

        if ($setting === null) {
            $setting = new SeoSetting([
                'company_id' => $companyId,
                'content' => null,
            ]);
        }

        return view('backend.' . $this->module . '.index', compact('setting', 'companyId'));
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'nullable|string',
        ]);

        $companyId = $this->companyIdFromConfig();

        try {
            SeoSetting::query()->updateOrCreate(
                ['company_id' => $companyId],
                ['content' => $request->input('content')]
            );

            ApiPayloadCache::invalidateRobotsTxt($companyId);

            return response()->json(['status' => true, 'notification' => __('messages.updated')]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'notification' => __('messages.failed')], 500);
        }
    }

    private function companyIdFromConfig(): int
    {
        $raw = config('custom.company_id');
        if (!is_numeric($raw) || (int) $raw <= 0) {
            return 0;
        }

        return (int) $raw;
    }
}
