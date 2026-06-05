<?php

use App\Models\Company;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Upload;
use Illuminate\Support\Facades\Auth;
use App\Models\Page;
use App\Models\PageMeta;
use Illuminate\Validation\Rule;

if (!function_exists('truncate_text')) {
    /**
     * Truncate a string to a specified length and append a suffix if needed.
     *
     * @param string $text
     * @param int $length
     * @param string $suffix
     * @return string
     */
    function truncateText($text, int $length = 15, string $suffix = '...'): string
    {
        if ($text === null || empty($text)) {
            return '';
        }    
        return \Illuminate\Support\Str::limit($text, $length, $suffix);
    }
}


if (!function_exists('getCompanyList')) {
    function getCompanyList()
    {
        $companies = auth()->user()?->company_id
            ? Company::where('id', auth()->user()->company_id)->get()
            : Company::all();

        // Add a custom display_name field (only for this helper)
        return $companies->map(function ($company) {
            $company->name = $company->name . ($company->website ? ' - ' . $company->website . '' : '');
            return $company;
        });
    }
}

if (!function_exists('getPageLayouts')) {
    /**
     * Get available layouts for pages.
     *
     * @param array|null $only  Optional list of layout keys to include
     * @return array<string, array{label: string, description: string}>
     */
    function getPageLayouts(array $only = null): array
    {
        $layouts = [            
            'default' => [
                'label' => 'Default',
                'description' => 'Standard content layout.',
            ],
            'example' => [
                'label' => 'Example',
                'description' => 'Example layout to demonstrate layout structure and fields.',
            ],            
        ];

        if ($only === null || $only === []) {
            return $layouts;
        }

        // Keep only requested layouts and preserve $layouts insertion order.
        $result = [];
        foreach ($layouts as $key => $data) {
            if (in_array($key, $only, true)) {
                $result[$key] = $data;
            }
        }

        return $result;
    }
}

if (! function_exists('formatDate')) {
    /**
     * Format date to dd/mm/yyyy.
     *
     * @param  string  $date
     * @return string
     */
    function formatDate($date)
    {
        // Check if the date is not null or empty
        if ($date) {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        }
        return null; // Return null if no date is provided
    }
}

if (! function_exists('formatDatetime')) {
    /**
     * Format date and time to dd/mm/yyyy h:i A (AM/PM).
     *
     * @param  string  $date
     * @return string|null
     */
    function formatDatetime($date)
    {
        // Check if the date is not null or empty
        if ($date) {
            return \Carbon\Carbon::parse($date)->format('d/m/Y h:i A');
        }
        return null; // Return null if no date is provided
    }
}


if (! function_exists('jsonDecodeAndPrint')) {
    /**
     * Decode a JSON string and return its values as a string.
     *
     * @param  string  $json
     * @param  string  $separator  The separator between items when printing (default is a comma)
     * @return string
     */
    function jsonDecodeAndPrint($json, $separator = ', ')
    {
        // Decode the JSON string into an array
        $decoded = json_decode($json, true);

        // Check for JSON errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            return "";  // Return error message if decoding fails
        }

        // Return the values as a string with the given separator
        return implode($separator, $decoded);
    }
}

if (!function_exists('currentUser')) {
    /**
     * Get the currently authenticated user.
     *
     * @return \App\Models\User|null
     */
    function currentUser()
    {
        return \App\Models\User::find(Auth::id());
    }
}

if (!function_exists('getYears')) {
    /**
     * Get an array of years from the specified start year to the end year.
     *
     * @param int $start The start year (default is 2020).
     * @param int $end The end year (default is 2050).
     * @return array An array containing the years from start to end (inclusive).
     */
    function getYears(int $start = 2020, int $end = 2050): array
    {
        // Ensure the start year is less than or equal to the end year
        if ($start > $end) {
            throw new InvalidArgumentException("Start year cannot be greater than end year.");
        }

        return range($start, $end);
    }
}

if (!function_exists('central_asset')) {
    function central_asset($path)
    {
        $baseUrl = rtrim(config('custom.assets_url', env('ASSETS_URL', env('APP_URL'))), '/');

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($path);
            $path = $parsedUrl['path'] ?? '';
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('uploaded_asset_name')) {
    function uploaded_asset_name($id) {

        $asset = Cache::rememberForever('uploaded_asset_name_'.$id , function() use ($id) {
            return \App\Models\Upload::find($id);
        });

        $filename = 'Unknown';

        if ($asset != null) {
            $filename = $asset->file_original_name;
        }
                
        // Extract filename without extension
        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        
        // Replace underscores and hyphens with spaces
        $formattedName = str_replace(['_', '-'], ' ', $nameWithoutExt);
        
        // Convert multiple spaces to a single space and trim excess spaces
        $formattedName = preg_replace('/\s+/', ' ', trim($formattedName));
    
        // Capitalize each word
        return ucwords($formattedName);
    }
}

if (!function_exists('uploaded_asset_type')) {
    function uploaded_asset_type($id) {

        $asset = Cache::rememberForever('uploaded_asset_type_'.$id , function() use ($id) {
            return \App\Models\Upload::find($id);
        });

        $filename = 'Unknown';

        if ($asset != null) {
            $filename = $asset->type;
        }
    
        // Capitalize each word
        return $filename;
    }
}

if (!function_exists('get_setting')) {
    function get_setting($metaKey, $default = null) {
        //return \Illuminate\Support\Facades\Cache::rememberForever("setting_" . config('custom.company_id') . "_{$metaKey}", function () use ($metaKey, $default) {
            $company = \App\Models\Company::with('meta')->where('id', config('custom.company_id'))->first();

            if (!$company) {
                return $default;
            }

            // First, check if the column exists in the companies table
            if (isset($company->$metaKey)) {
                return $company->$metaKey;
            }

            // Otherwise, check the meta table
            return $company->meta->where('meta_key', $metaKey)->first()->meta_value ?? $default;
        //});
    }
}

if (!function_exists('backend_logo_url')) {
    /**
     * Get the backend logo URL with a safe fallback.
     *
     * @return string
     */
    function backend_logo_url(): string
    {
        $backendLogoId = get_setting('logo');
        $logoPath = $backendLogoId ? uploaded_asset($backendLogoId) : null;

        if (!empty($logoPath)) {
            return $logoPath;
        }

        return asset('assets/backend/img/logo.png');
    }
}

if (!function_exists('makeImageThumbnail')) {
    function makeImageThumbnail($relativePath, $width = 150, $height = 150, $quality = 80) {
        try {
            $publicPath = public_path($relativePath);

            if (!file_exists($publicPath)) {
                return null;
            }

            $pathInfo = pathinfo($relativePath);
            $originalFileName = $pathInfo['basename']; // abc.jpg

            // Thumbnail directory (e.g., storage/thumbs/150x150/)
            $thumbDir = "storage/thumbs/{$width}x{$height}";
            $thumbRelativePath = "{$thumbDir}/{$originalFileName}";
            $thumbPublicPath = public_path($thumbRelativePath);

            // Create the directory if it doesn't exist
            if (!file_exists(public_path($thumbDir))) {
                mkdir(public_path($thumbDir), 0777, true);
            }

            // Only create thumbnail if it doesn't exist
            if (!file_exists($thumbPublicPath)) {
                $manager = new ImageManager(new Driver());

                $manager->read($publicPath)
                    ->cover($width, $height)
                    ->save($thumbPublicPath, $quality);
            }

            return asset($thumbRelativePath);

        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('text_limit')) {
    function text_limit($text, $limit = 15)
    {
        return \Illuminate\Support\Str::limit($text, $limit);
    }
}

if (! function_exists('formatCurrency')) {
    /**
     * Format an amount as Indian Rupees (₹).
     *
     * @param  float|int|string|null  $amount
     * @param  int  $decimals
     * @return string|null
     */
    function formatCurrency($amount, int $decimals = 2): ?string
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        return '₹' . number_format((float) $amount, $decimals);
    }
}

if (! function_exists('formatBookingId')) {
    /**
     * Format a booking ID with prefix and zero padding.
     *
     * @param  int|string|null  $id
     * @return string|null
     */
    function formatBookingId($id): ?string
    {
        if ($id === null || $id === '') {
            return null;
        }

        $prefix = (string) config('custom.booking_id_prefix', 'BK-');
        $padding = max(1, (int) config('custom.booking_id_padding', 6));

        return $prefix.str_pad((string) (int) $id, $padding, '0', STR_PAD_LEFT);
    }
}
