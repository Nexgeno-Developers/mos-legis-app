<?php

use App\Models\Company;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use App\Models\TinyMCEKey;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Upload;
use Illuminate\Support\Facades\Auth;
use App\Models\Page;
use App\Models\PageMeta;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Post;
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

if (!function_exists('normalize_ids')) {
    function normalize_ids($ids): array
    {
        if (empty($ids)) return [];

        // If already array
        if (is_array($ids)) {
            return array_map('intval', $ids);
        }

        // If JSON array string "[1,2]"
        if (is_string($ids) && str_starts_with($ids, '[')) {
            $decoded = json_decode($ids, true);
            return is_array($decoded) ? array_map('intval', $decoded) : [];
        }

        // If comma separated "1,2"
        if (is_string($ids) && str_contains($ids, ',')) {
            return array_map('intval', explode(',', $ids));
        }

        // Single ID
        return [(int) $ids];
    }
}

if (!function_exists('page_details_from_ids')) {
    function page_details_from_ids($ids, bool $returnSingleWhenOne = true)
    {
        $ids = normalize_ids($ids);

        if (empty($ids)) return null;

        $pages = Page::whereIn('id', $ids)
            ->get(['id', 'title', 'slug'])
            ->toArray();

        if (empty($pages)) return $returnSingleWhenOne ? null : [];

        // Get page IDs for meta lookup
        $pageIds = array_column($pages, 'id');

        // Fetch all relevant meta in one query
        $metaKeys = ['short_summary_icon', 'short_summary_image', 'short_summary_title', 'short_summary_description', 'short_summary_video_url'];
        $pageMetas = PageMeta::whereIn('page_id', $pageIds)
            ->whereIn('meta_key', $metaKeys)
            ->get()
            ->groupBy('page_id');

        // Process each page and add meta fields
        foreach ($pages as &$page) {
            $metaGroup = $pageMetas->get($page['id'], collect());
            $metaMap = $metaGroup->pluck('meta_value', 'meta_key')->toArray();

            // Upload fields
            if (!empty($metaMap['short_summary_icon'])) {
                $page['short_summary_icon'] = uploaded_asset_details_from_ids($metaMap['short_summary_icon']);
            }

            if (!empty($metaMap['short_summary_image'])) {
                $page['short_summary_image'] = uploaded_asset_details_from_ids($metaMap['short_summary_image']);
            }

            // Text fields
            if (!empty($metaMap['short_summary_video_url'])) {
                $page['short_summary_video_url'] = $metaMap['short_summary_video_url'];
            }

            if (!empty($metaMap['short_summary_title'])) {
                $page['short_summary_title'] = $metaMap['short_summary_title'];
            }

            if (!empty($metaMap['short_summary_description'])) {
                $page['short_summary_description'] = $metaMap['short_summary_description'];
            }
        }

        if ($returnSingleWhenOne && count($pages) === 1) {
            return $pages[0];
        }

        return $pages;
    }
}

if (!function_exists('post_category_details_from_ids')) {
    function post_category_details_from_ids($ids, bool $returnSingleWhenOne = true)
    {
        $ids = normalize_ids($ids);

        if (empty($ids)) return null;

        $categories = Category::whereIn('id', $ids)
            ->get(['id', 'name', 'slug', 'description', 'breadcrumb_image']);

        if ($categories->isEmpty()) {
            return $returnSingleWhenOne ? null : [];
        }

        $companyId = config('custom.company_id');

        $categoryPayloads = $categories->map(function (Category $category) use ($companyId) {
            $postsQuery = $category->posts()
                ->where('is_active', true)
                ->with('meta')
                ->orderByDesc('published_at')
                ->limit(3);

            if (!empty($companyId)) {
                $postsQuery->where('company_id', $companyId);
            }

            $posts = $postsQuery->get()->map(function (Post $post) {
                $summary = post_meta_value($post, 'short_summary');
                if (!filled($summary)) {
                    $summary = post_meta_value($post, 'summary');
                }

                $date = post_meta_value($post, 'date');
                $time = post_meta_value($post, 'time');

                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'featured_image' => filled($post->featured_image)
                        ? uploaded_asset_details_from_ids($post->featured_image)
                        : null,
                    'summary' => $summary,
                    'date' => filled($date) ? $date : null,
                    'time' => filled($time) ? $time : null,
                ];
            })->values()->all();

            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                // 'breadcrumb_image' => filled($category->breadcrumb_image)
                //     ? uploaded_asset_details_from_ids($category->breadcrumb_image)
                //     : null,
                'posts' => $posts,
            ];
        })->values()->all();

        if ($returnSingleWhenOne && count($categoryPayloads) === 1) {
            return $categoryPayloads[0];
        }

        return $categoryPayloads;
    }
}
