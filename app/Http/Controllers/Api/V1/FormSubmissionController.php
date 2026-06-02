<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\FormSubmissionMail;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class FormSubmissionController extends Controller
{
    /**
     * Submit a form via API.
     *
     * Expected:
     * - `form_name`
     * - `name`, `email`, `phone` (depending on form_name rules)
     * - Any other validated scalar fields as per `getValidationRules()`
     * - Uploaded files as top-level multipart form-data fields (e.g. `image`, `resume`, `pdf`, etc.)
     *
     * Uploaded file paths will be stored inside `forms.form_data` under the same field name.
     */
    public function submit(Request $request)
    {
        $formName = $request->input('form_name');
        if (!$formName) {
            return response()->json([
                'error' => [
                    'message' => 'form_name is required',
                    'code' => 'FORM_NAME_REQUIRED',
                ],
            ], 422);
        }

        $validationRules = $this->getValidationRules($formName);
        $validatedData = $request->validate($validationRules);



        $companyId = $request->input('company_id') ?? 1;

        // Keep parity with your web controller: store only validated scalar fields
        // (excluding name/email/phone/form_name/company_id).
        $formData = collect($validatedData)
            ->except(['form_name', 'name', 'email', 'phone'])
            ->toArray();

        // Add uploaded files (multipart) into form_data under their input field name.
        // We store a storage-relative public path (prefixed with `storage/`)
        // so the backend can render it using `my_asset()`.
        $files = $request->allFiles();
        foreach ($files as $field => $fileValue) {
            $stored = $this->storeFileValue($fileValue, $formName, (string) $companyId);
            if ($stored === null) {
                continue;
            }

            $formData[$field] = $stored;
        }

        $name = $request->input('name');

        if (empty($name)) {
            $first = $request->input('first_name') ?? '';
            $last  = $request->input('last_name') ?? '';
            $name = trim($first . ' ' . $last);
        }        

        $form = Form::create([
            'form_name' => $formName,
            'name' => $name,
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'form_data' => $formData,
            'ip' => $request->ip(),
            'company_id' => $companyId,
        ]);


        // Keep the same behavior as the web flow (send email to your configured recipient).
        // Best-effort integration: do not block the main submission if the external API fails.
        $this->sendLeadToExternalApiBestEffort($request, $formName, (int) $form->id);

        // Keep the same behavior as the web flow (send email to your configured recipient).
        // $recipientEmail = [config('custom.from_email')];
        // try {
        //     Mail::to($recipientEmail)->send(new FormSubmissionMail($formName, array_merge($validatedData, $formData)));
        // } catch (\Throwable $e) {
        //     // Avoid failing the submission if email fails.
        //     logger('Form submission mail failed: ' . $e->getMessage());
        // }

        return response()->json([
            'data' => [
                'id' => $form->id,
                'form_name' => $form->form_name,
                'created_at' => $form->created_at,
                'form_data' => $form->form_data,
            ],
        ], 201);
    }

    /**
     * Send lead data to the external endpoint (query-string based POST).
     * This function never throws; it only logs failures.
     */
    private function sendLeadToExternalApiBestEffort(Request $request, string $formName, int|string $formId): void
    {
        try {
            $endpoint = (string) (config('services.lamipak_lead.endpoint')
                ?? env('LAMIPAK_LEAD_ENDPOINT'));

            $url = $this->buildExternalLeadApiUrl($endpoint, $request, $formName);

            // Best-effort: external call should not hang the main API.
            if (!function_exists('curl_init')) {
                logger('External lead API: curl extension is not available', [
                    'form_id' => $formId,
                    'form_name' => $formName,
                ]);
                return;
            }

            $ch = curl_init();
            if ($ch === false) {
                logger('External lead API: curl_init failed', ['form_id' => $formId, 'form_name' => $formName]);
                return;
            }

            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => '',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 15,
                // The endpoint may respond with 30x redirects; follow so we observe final status.
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($ch);
            $curlErr = curl_error($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $responsePreview = is_string($response) ? substr($response, 0, 1000) : '';

            if (!empty($curlErr)) {
                logger('External lead API: CURL error', [
                    'form_id' => $formId,
                    'form_name' => $formName,
                    'curl_error' => $curlErr,
                    'response_preview' => $responsePreview,
                ]);
                return;
            }

            if ($httpCode >= 400 || $httpCode === 0) {
                logger('External lead API: HTTP error', [
                    'form_id' => $formId,
                    'form_name' => $formName,
                    'http_code' => $httpCode,
                    'response_preview' => $responsePreview,
                ]);
                return;
            }

            logger('External lead API: success', [
                'form_id' => $formId,
                'form_name' => $formName,
                'http_code' => $httpCode,
                'response_preview' => $responsePreview,
            ]);
        } catch (\Throwable $e) {
            logger('External lead API: unexpected exception', [
                'form_name' => $formName,
                'form_id' => $formId,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Build the external API URL with query parameters.
     *
     * Rules:
     * - For missing scalar fields: use '-'
     * - For `company` (string array): if missing/empty => send one empty `company` param; if provided => send repeats.
     */
    private function buildExternalLeadApiUrl(string $endpoint, Request $request, string $formName): string
    {
        $scalarKeys = [
            'first_name',
            'last_name',
            'email',
            'Job_Position',
            'Interested',
            'Subject',
            'Message',
            'source',
            'Download_Resource',
            'Commit_Date',
            'Commit_Time',
            'Commit_Zone',
            'phone',
            'job_title',
            'website',
            'Interested_Products',
            'Interested_Marketing_Support_Service',
            'Interested_Technical_Support_Service',
        ];

        $arrayKeys = ['company'];

        $pairs = [];

        foreach ($scalarKeys as $key) {
            $pairs[] = [$key, $this->scalarValueOrDefault($request, $key, $formName)];
        }

        foreach ($arrayKeys as $key) {
            foreach ($this->stringArrayValueOrEmpty($request, $key) as $item) {
                $pairs[] = [$key, $item];
            }
        }

        $queryParts = [];
        foreach ($pairs as [$key, $value]) {
            // `urlencode` uses `+` for spaces, matching your sample curl query encoding.
            $queryParts[] = urlencode((string) $key) . '=' . urlencode((string) $value);
        }

        $queryString = implode('&', $queryParts);

        return rtrim($endpoint, '?') . ($queryString !== '' ? '?' . $queryString : '');
    }

    private function scalarValueOrDash(Request $request, string $key): string
    {
        // External lead API expects some TitleCase keys, while our public API uses snake_case.
        // If the TitleCase key is missing, fall back to its snake_case counterpart.
        $resolvedKey = $key;
        if (!$request->exists($resolvedKey)) {
            $alias = match ($key) {
                'Job_Position' => 'job_title',
                'Interested' => 'interests',
                'Message' => 'message',
                'Interested_Products' => 'interested_products',
                'Interested_Marketing_Support_Service' => 'interested_marketing_support_service',
                'Interested_Technical_Support_Service' => 'interested_technical_support_service',
                default => null,
            };

            if ($alias && $request->exists($alias)) {
                $resolvedKey = $alias;
            } else {
                return '-';
            }
        }

        $value = $request->input($resolvedKey);
        if ($value === null) {
            return '-';
        }

        if (is_array($value)) {
            // Client sent an unexpected type for a scalar field.
            // Keep behavior predictable: treat as missing.
            return '-';
        }

        return (string) $value;
    }

    private function scalarValueOrDefault(Request $request, string $key, string $formName): string
    {
        if ($key === 'source') {
            if (!$request->exists('source')) {
                // Missing fields should follow the "use '-'" rule.
                return '-';
            }

            $value = $request->input('source');
            if ($value === null) {
                return $this->defaultSourceByFormName($formName);
            }

            if (is_array($value)) {
                // Unexpected type for `source`.
                return '-';
            }

            $string = (string) $value;
            return trim($string) === '' ? $this->defaultSourceByFormName($formName) : $string;
        }

        if ($key === 'website') {
            $resolvedKey = 'website';
            if (!$request->exists('website') && $request->exists('company_url')) {
                $resolvedKey = 'company_url';
            }

            if (!$request->exists($resolvedKey)) {
                // Missing fields should follow the "use '-'" rule.
                return '-';
            }

            $value = $request->input($resolvedKey);
            if ($value === null) {
                return 'https://www.lamipak.biz/';
            }

            if (is_array($value)) {
                return 'https://www.lamipak.biz/';
            }

            $string = (string) $value;
            return trim($string) === '' ? 'https://www.lamipak.biz/' : $string;
        }

        return $this->scalarValueOrDash($request, $key);
    }

    private function defaultSourceByFormName(string $formName): string
    {
        return match ($formName) {
            'subscription form' => 'subscription',
            'subscription' => 'subscription',
            'get_in_touch' => 'Technical Experts form',
            'contact' => 'contact us form',
            default => '-',
        };
    }

    /**
     * @return string[]
     */
    private function stringArrayValueOrEmpty(Request $request, string $key): array
    {
        if (!$request->exists($key)) {
            return $key === 'company' ? [''] : [];
        }

        $value = $request->input($key);
        if ($value === null) {
            return $key === 'company' ? [''] : [];
        }

        if (is_array($value)) {
            if (count($value) === 0) {
                return $key === 'company' ? [''] : [];
            }

            $items = [];
            foreach ($value as $v) {
                if (is_scalar($v)) {
                    $items[] = (string) $v;
                }
            }

            if (count($items) === 0) {
                return $key === 'company' ? [''] : [];
            }

            return $items;
        }

        // Support `company` being sent as a single string.
        if (is_scalar($value)) {
            $string = (string) $value;
            return $string === '' ? ($key === 'company' ? [''] : []) : [$string];
        }

        return [];
    }

    /**
     * Store either:
     * - a single UploadedFile
     * - an array of UploadedFile
     *
     * Returns:
     * - string path for a single file
     * - string[] for multiple files
     * - null if the provided value isn't a valid UploadedFile (ignored)
     */
    private function storeFileValue(mixed $fileValue, string $formName, string $companyId): array|string|null
    {
        if ($fileValue instanceof UploadedFile) {
            return $this->storeOneFile($fileValue, $formName, $companyId);
        }

        if (is_array($fileValue)) {
            $paths = [];
            foreach ($fileValue as $maybeFile) {
                if ($maybeFile instanceof UploadedFile) {
                    $paths[] = $this->storeOneFile($maybeFile, $formName, $companyId);
                }
            }

            if (count($paths) === 0) {
                return null;
            }

            return count($paths) === 1 ? $paths[0] : $paths;
        }

        return null;
    }

    private function storeOneFile(UploadedFile $file, string $formName, string $companyId): string
    {
        // Match the intent of ProtectForms middleware.
        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
        ];

        $mimeType = (string) $file->getMimeType();
        if (!in_array($mimeType, $allowedMimes, true)) {
            abort(422, 'Disallowed file type');
        }

        $maxSizeBytes = 10 * 1024 * 1024; // 10MB (keeps things reasonable for forms)
        if ($file->getSize() > $maxSizeBytes) {
            abort(422, 'File too large');
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $date = date('Y/m');

        // Store on the `public` disk and return the storage-relative public path.
        $path = $file->storeAs(
            'uploads/forms/' . $formName . '/' . $companyId . '/' . $date,
            Str::random(20) . '.' . $extension,
            'public'
        );

        // The stored value is designed to be compatible with `my_asset($value)`.
        return 'storage/' . $path;
    }

    private function getValidationRules(string $formName): array
    {
        switch ($formName) {
            case 'subscription':
                return [
                    'form_name' => 'required|max:20',
                    'email' => 'required|email|max:50',
                ];

            case 'get_in_touch':
                return [
                    'form_name' => 'required|max:20',
                    'name' => 'required|string|max:50',
                    'email' => 'required|email|max:50',
                ];

            case 'message':
                return [
                    'form_name' => 'required|max:20',
                    'name' => 'nullable|string|max:50',
                    'first_name' => 'required|string|max:50',
                    'last_name' => 'required|string|max:50',
                    'phone' => 'nullable|digits_between:10,15|max:50',
                    'email' => 'required|email|max:50',
                    'message' => 'nullable|string|max:200',
                ]; 
                
            case 'contact':
                return [
                    'form_name' => 'required|max:20',
                    'name' => 'nullable|string|max:50',
                    'first_name' => 'required|string|max:50',
                    'last_name' => 'required|string|max:50',
                    'email' => 'required|email|max:50',
                    'phone' => 'nullable|digits_between:10,15|max:50',
                    'company_name' => 'required|string|max:50',
                    'company_url' => 'nullable|string|max:255',
                    'job_function'=> 'required|string|max:50',
                    'job_title'   => 'required|string|max:50',
                    'country'     => 'required|string|max:50',
                    'interests' => 'required|string|max:200',
                    'interested_products' => 'nullable|string|max:200|required_if:interests,Products & Services',
                    'interested_marketing_support_service' => 'nullable|string|max:200|required_if:interests,Products & Services',
                    'interested_technical_support_service' => 'nullable|string|max:200|required_if:interests,Products & Services',
                    'message' => 'nullable|string|max:200',
                ];                  

            default:
                return [
                    'form_name' => 'required|max:20',
                ];
        }
    }
}