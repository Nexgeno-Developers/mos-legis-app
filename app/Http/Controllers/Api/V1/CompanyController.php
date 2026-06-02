<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\ApiPayloadCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    /**
     * Company meta keys that store upload id(s) (e.g. "4" or "1,3,5").
     *
     * We resolve these into filename + url.
     *
     * @var string[]
     */
    private array $uploadMetaKeys = [
        'favicon',
        'og_image',
        'twitter_image',
        'breadcrumb',
        'technical_experts_image',
    ];

    /**
     * Fetch a company (and its key/value meta) by id.
     */
    public function showById(string $id): JsonResponse
    {
        if (!ctype_digit($id)) {
            return response()->json([
                'error' => [
                    'message' => 'Company not found',
                    'code' => 'COMPANY_NOT_FOUND',
                ],
            ], 404);
        }

        $companyIdAsInt = (int) $id;

        // Optional security: if the client is authenticated and is tied to a company,
        // only allow fetching that company.
        if (Auth::check()) {
            $companyId = Auth::user()->company_id ?? null;

            if ($companyId && (int) $companyId !== $companyIdAsInt) {
                return response()->json([
                    'error' => [
                        'message' => 'Company not accessible',
                        'code' => 'COMPANY_NOT_ACCESSIBLE',
                    ],
                ], 403);
            }
        }

        $cached = ApiPayloadCache::getCachedCompanyPayload($companyIdAsInt);
        if ($cached !== null) {
            return response()->json(['data' => $cached]);
        }

        $company = Company::query()
            ->with('meta')
            ->where('id', $companyIdAsInt)
            ->first();

        if (!$company) {
            return response()->json([
                'error' => [
                    'message' => 'Company not found',
                    'code' => 'COMPANY_NOT_FOUND',
                ],
            ], 404);
        }

        $payload = $this->companyPayload($company);
        ApiPayloadCache::storeCompanyPayload((int) $company->id, $payload);

        return response()->json([
            'data' => $payload,
        ]);
    }

    private function companyPayload(Company $company): array
    {
        $logo = uploaded_asset_details_from_ids($company->logo);
        $footerLogo = uploaded_asset_details_from_ids($company->footer_logo_image);
        $googleMap = uploaded_asset_details_from_ids($company->google_map);

        return [
            'id' => $company->id,
            'name' => $company->name,
            'logo' => $logo,
            'footer_logo_image' => $footerLogo,
            'email' => $company->email,
            'phone' => $company->phone,
            'whatsapp' => $company->whatsapp,
            'address' => $company->address,
            'website' => $company->website,
            'google_map' => $googleMap,
            'short_description' => $company->short_description,
            'meta_title' => $company->meta_title,
            'meta_description' => $company->meta_description,
            'is_active' => (bool) $company->is_active,
            'meta' => $company->meta
                ->map(fn ($m) => [
                    $m->meta_key => in_array($m->meta_key, $this->uploadMetaKeys, true)
                        ? uploaded_asset_details_from_ids($m->meta_value)
                        : $m->meta_value,
                ])
                ->values()
                ->all(),
        ];
    }
}
