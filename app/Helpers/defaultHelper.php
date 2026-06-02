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

if (!function_exists('convertToSlug')) {
    /**
     * Convert text to a slug format.
     *
     * @param  string  $text
     * @return string
     */
    function convertToSlug(string $text): string
    {
        if (empty($text)) {
            return '';
        }
        
        return \Illuminate\Support\Str::slug($text);
    }
}

if (!function_exists('normalize_post_slug')) {
    /**
     * Normalize post slugs while allowing path-like segments (e.g. "section/item/..").
     */
    function normalize_post_slug(string $slug): string
    {
        $slug = trim($slug);
        $slug = trim($slug, '/');
        $slug = preg_replace('/\s+/', '-', $slug);

        return strtolower($slug ?? '');
    }
}

if (!function_exists('slug_conflicts_with_pages_or_posts')) {
    /**
     * Check if a slug exists in pages or posts (including post auto_slug).
     */
    function slug_conflicts_with_pages_or_posts(
        string $slug,
        ?int $companyId = null,
        ?int $ignorePageId = null,
        ?int $ignorePostId = null
    ): bool {
        $normalized = normalize_post_slug($slug);

        if ($normalized === '') {
            return false;
        }

        $pageQuery = Page::query()->where('slug', $normalized);
        if (!empty($companyId)) {
            $pageQuery->where('company_id', $companyId);
        }
        if (!empty($ignorePageId)) {
            $pageQuery->where('id', '!=', $ignorePageId);
        }
        if ($pageQuery->exists()) {
            return true;
        }

        $postQuery = Post::query()->where('slug', $normalized);
        if (!empty($companyId)) {
            $postQuery->where('company_id', $companyId);
        }
        if (!empty($ignorePostId)) {
            $postQuery->where('id', '!=', $ignorePostId);
        }
        if ($postQuery->exists()) {
            return true;
        }

        $autoQuery = Post::query()->whereJsonContains('auto_slug', $normalized);
        if (!empty($companyId)) {
            $autoQuery->where('company_id', $companyId);
        }
        if (!empty($ignorePostId)) {
            $autoQuery->where('id', '!=', $ignorePostId);
        }

        return $autoQuery->exists();
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
            // 'example' => [
            //     'label' => 'Example',
            //     'description' => 'Example layout to demonstrate layout structure and fields.',
            // ],            
            'home' => [
                'label' => 'Home',
                'description' => 'Homepage layout for the website.',
            ],
            // Product Categories
            'product_categories' => [
                'label' => 'Product Categories',
                'description' => 'Product Categories List.',
            ],            
            'product_category_detail_1' => [
                'label' => 'Product Category Layout 1',
                'description' => 'Product Categories Detail Layout 1.',
            ],
            'product_category_detail_2' => [
                'label' => 'Product Category Layout 2',
                'description' => 'Product Categories Detail Layout 2.',
            ],
            'product_category_detail_3' => [
                'label' => 'Product Category Layout 3',
                'description' => 'Product Categories Detail Layout 3.',
            ],
            'product_category_detail_4' => [
                'label' => 'Product Category Layout 4',
                'description' => 'Product Categories Detail Layout 4.',
            ],
            'product_category_detail_5' => [
                'label' => 'Product Category Layout 5',
                'description' => 'Product Categories Detail Layout 5.',
            ],

            // Product Industries
            'product_industries' => [
                'label' => 'Product Industries',
                'description' => 'Product Industries layout.',
            ],  
            'product_industry_detail' => [
                'label' => 'Product Industry Detail',
                'description' => 'Product Industry Detail layout.',
            ],  
            
            // Product Industries
            'products' => [
                'label' => 'Products',
                'description' => 'Product Detail layout.',
            ],              
            
            //Marketing Services
            'marketing_services' => [
                'label' => 'Marketing Services',
                'description' => 'Marketing Services layout.',
            ],  

            'marketing_service_detail' => [
                'label' => 'Marketing Service Detail',
                'description' => 'Marketing Service Detail layout.',
            ],
            
            //Technical Services
            'technical_services' => [
                'label' => 'Technical Services',
                'description' => 'technical Services layout.',
            ],  

            'technical_service_detail' => [
                'label' => 'Technical Service Detail',
                'description' => 'Technical Service Detail layout.',
            ],  
            
            //Sustainability
            'sustainability_1' => [
                'label' => 'Sustainability 1',
                'description' => 'Sustainability 1',
            ],
            'sustainability_2' => [
                'label' => 'Sustainability 2',
                'description' => 'Sustainability 2',
            ],
            'sustainability_3' => [
                'label' => 'Sustainability 3',
                'description' => 'Sustainability 3',
            ],
            'sustainability_4' => [
                'label' => 'Sustainability 4',
                'description' => 'Sustainability 4',
            ],
            'sustainability_5' => [
                'label' => 'Sustainability 5',
                'description' => 'Sustainability 5',
            ],
            'sustainability_6' => [
                'label' => 'Sustainability 6',
                'description' => 'Sustainability 6',
            ],    
            
            // About Us
            'about_1' => [
                'label' => 'About 1',
                'description' => 'About - Journeys',
            ],
            'about_2' => [
                'label' => 'About 2',
                'description' => 'About - About Us',
            ],
            'about_3' => [
                'label' => 'About 3',
                'description' => 'About - Mission & Vision',
            ],
            'about_4' => [
                'label' => 'About 4',
                'description' => 'About - Governance',
            ],
            
            // Careers
            'career' => [
                'label' => 'Career',
                'description' => 'Career layout.',
            ],

            // RnD center
            'rnd_center' => [
                'label' => 'R&D Center',
                'description' => 'R&D Center layout.',
            ],

            // Innovation
            'innovation' => [
                'label' => 'Innovations',
                'description' => 'Innovations layout.',
            ],

            'innovation_detail_1' => [
                'label' => 'Innovation Detail 1',
                'description' => 'Innovation Detail layout.',
            ],

            'innovation_detail_2' => [
                'label' => 'Innovation Detail 2',
                'description' => 'Innovation Detail layout.',
            ],

            // insights & media
            'insights_n_media' => [
                'label' => 'Insights & Media',
                'description' => 'Insights & Media layout.',
            ],

            // Contact Us
            'contact_us' => [
                'label' => 'Contact Us',
                'description' => 'Contact Us layout.',
            ],

            // Thank You
            'thank_us' => [
                'label' => 'Thank You',
                'description' => 'Thank You layout.',
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

if (!function_exists('getPostLayoutConfig')) {
    /**
     * Centralized layout configuration for posts.
     *
     * @return array<string, array{label:string, description:string, sections:array}>
     */
    function getPostLayoutConfig(): array
    {
        return [
            'default_post_detail' => [
                'label' => 'Default',
                'description' => 'Default post layout.',
                'sections' => [
                    [
                        'title' => 'Header',
                        'fields' => [
                            // [
                            //     'key' => 'subtitle',
                            //     'label' => 'Subtitle',
                            //     'type' => 'text',
                            //     'placeholder' => 'Optional subtitle',
                            //     'rules' => 'nullable|string|max:255',
                            // ],
                            [
                                'key' => 'date',
                                'label' => 'Date',
                                'type' => 'date',
                                'rules' => 'nullable|date_format:Y-m-d',
                            ],
                            [
                                'key' => 'time',
                                'label' => 'Time',
                                'type' => 'time',
                                'rules' => 'nullable|date_format:H:i',
                            ],
                            [
                                'key' => 'summary',
                                'label' => 'Summary',
                                'type' => 'textarea',
                                'placeholder' => 'Detailed summary (optional)',
                                'rules' => 'nullable|string|max:800',
                            ],
                            // [
                            //     'key' => 'hero_image',
                            //     'label' => 'Hero Image',
                            //     'type' => 'image',
                            //     'rules' => 'nullable|string',
                            // ],
                        ],
                    ],
                    [
                        'title' => 'Right Side Blocks',
                        'fields' => [
                            [
                                'key' => 'right_side_blocks',
                                'label' => 'Right Side Blocks',
                                'type' => 'repeater',
                                'item_label' => 'Block',
                                'rules' => 'nullable|array',
                                'fields' => [
                                    [
                                        'key' => 'image',
                                        'label' => 'Image',
                                        'type' => 'image',
                                        'rules' => 'nullable|string',
                                    ],
                                    [
                                        'key' => 'url',
                                        'label' => 'URL',
                                        'type' => 'text',
                                        'placeholder' => 'https://',
                                        'rules' => 'nullable|string|max:500',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}

if (!function_exists('getPostLayouts')) {
    /**
     * Get available layouts for posts.
     *
     * @param array|null $only
     * @return array<string, array{label:string, description:string}>
     */
    function getPostLayouts(array $only = null): array
    {
        $layouts = [];
        foreach (getPostLayoutConfig() as $key => $config) {
            $layouts[$key] = [
                'label' => $config['label'] ?? ucfirst($key),
                'description' => $config['description'] ?? '',
            ];
        }

        if ($only === null || $only === []) {
            return $layouts;
        }

        $result = [];
        foreach ($layouts as $key => $data) {
            if (in_array($key, $only, true)) {
                $result[$key] = $data;
            }
        }

        return $result;
    }
}

if (!function_exists('getPostLayoutSections')) {
    /**
     * Get layout section definitions for a layout key.
     */
    function getPostLayoutSections(string $layout): array
    {
        $config = getPostLayoutConfig();

        // Backward/forward compatibility when renaming layout keys.
        if (!isset($config[$layout])) {
            if ($layout === 'default_detail' && isset($config['default_post_detail'])) {
                $layout = 'default_post_detail';
            } elseif ($layout === 'default_post_detail' && isset($config['default_detail'])) {
                $layout = 'default_detail';
            }
        }

        return $config[$layout]['sections'] ?? [];
    }
}

if (!function_exists('post_meta_is_json_type')) {
    function post_meta_is_json_type(string $type): bool
    {
        return in_array($type, ['multiselect', 'checkbox', 'repeater'], true);
    }
}

if (!function_exists('post_meta_is_upload_type')) {
    function post_meta_is_upload_type(string $type): bool
    {
        return in_array($type, ['image', 'images', 'file', 'files'], true);
    }
}

if (!function_exists('getPostMetaTypeMap')) {
    function getPostMetaTypeMap(): array
    {
        $map = [];
        foreach (getPostLayoutConfig() as $layout) {
            foreach ($layout['sections'] ?? [] as $section) {
                foreach ($section['fields'] ?? [] as $field) {
                    if (!empty($field['key']) && !empty($field['type'])) {
                        $map[$field['key']] = $field['type'];
                    }
                }
            }
        }

        return $map;
    }
}

if (!function_exists('getPostMetaUploadKeys')) {
    function getPostMetaUploadKeys(): array
    {
        $map = getPostMetaTypeMap();

        return array_values(array_keys(array_filter($map, static function ($type) {
            return post_meta_is_upload_type($type);
        })));
    }
}

if (!function_exists('getPostMetaJsonKeys')) {
    function getPostMetaJsonKeys(): array
    {
        $map = getPostMetaTypeMap();

        return array_values(array_keys(array_filter($map, static function ($type) {
            return post_meta_is_json_type($type);
        })));
    }
}

if (!function_exists('getPostRepeaterFieldsMap')) {
    /**
     * Map repeater meta keys to their field definitions.
     *
     * @return array<string, array<string, string>>
     */
    function getPostRepeaterFieldsMap(): array
    {
        $map = [];

        foreach (getPostLayoutConfig() as $layout) {
            foreach ($layout['sections'] ?? [] as $section) {
                foreach ($section['fields'] ?? [] as $field) {
                    if (($field['type'] ?? '') !== 'repeater') {
                        continue;
                    }

                    $key = $field['key'] ?? null;
                    if (!$key) {
                        continue;
                    }

                    $subFields = [];
                    foreach ($field['fields'] ?? [] as $subField) {
                        $subKey = $subField['key'] ?? null;
                        if (!$subKey) {
                            continue;
                        }
                        $subFields[$subKey] = $subField['type'] ?? 'text';
                    }

                    $map[$key] = $subFields;
                }
            }
        }

        return $map;
    }
}

if (!function_exists('post_build_repeater_blocks')) {
    /**
     * Build list of repeater blocks from stored meta JSON.
     *
     * @param array $decoded
     * @param array<string,string> $fieldMap
     * @return array<int, array<string, mixed>>
     */
    function post_build_repeater_blocks(array $decoded, array $fieldMap): array
    {
        if (empty($decoded)) {
            return [];
        }

        // If already list of blocks, normalize and return.
        $isList = array_keys($decoded) === range(0, count($decoded) - 1);
        if ($isList) {
            return array_values(array_filter(array_map(function ($item) use ($fieldMap) {
                if (!is_array($item)) {
                    return null;
                }
                $block = [];
                foreach ($fieldMap as $fieldKey => $fieldType) {
                    $raw = $item[$fieldKey] ?? null;
                    if (post_meta_is_upload_type($fieldType)) {
                        $block[$fieldKey] = filled($raw) ? uploaded_asset_details_from_ids($raw) : null;
                    } else {
                        $block[$fieldKey] = $raw;
                    }
                }
                return $block;
            }, $decoded)));
        }

        $itrations = $decoded['itration'] ?? [];
        if (!is_array($itrations)) {
            $itrations = [];
        }

        $blocks = [];
        foreach ($itrations as $index => $itration) {
            $block = [];
            foreach ($fieldMap as $fieldKey => $fieldType) {
                $raw = $decoded[$fieldKey][$index] ?? null;
                if (post_meta_is_upload_type($fieldType)) {
                    $block[$fieldKey] = filled($raw) ? uploaded_asset_details_from_ids($raw) : null;
                } else {
                    $block[$fieldKey] = $raw;
                }
            }
            $blocks[] = $block;
        }

        return $blocks;
    }
}

if (!function_exists('getPostLayoutMetaKeys')) {
    function getPostLayoutMetaKeys(string $layout): array
    {
        $keys = [];
        foreach (getPostLayoutSections($layout) as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                if (!empty($field['key'])) {
                    $keys[] = $field['key'];
                }
            }
        }

        return $keys;
    }
}

if (!function_exists('getPostLayoutValidationRules')) {
    function getPostLayoutValidationRules(string $layout): array
    {
        $rules = [];

        foreach (getPostLayoutSections($layout) as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                $key = $field['key'] ?? null;
                if (!$key) {
                    continue;
                }

                if (!empty($field['rules'])) {
                    $rules['meta.' . $key] = $field['rules'];
                }

                if (($field['type'] ?? '') === 'repeater') {
                    foreach (($field['fields'] ?? []) as $subField) {
                        $subKey = $subField['key'] ?? null;
                        if (!$subKey) {
                            continue;
                        }
                        if (!empty($subField['rules'])) {
                            $rules['meta.' . $key . '.' . $subKey . '.*'] = $subField['rules'];
                        }
                    }
                } elseif (!empty($field['item_rules']) && post_meta_is_json_type($field['type'] ?? '')) {
                    $rules['meta.' . $key . '.*'] = $field['item_rules'];
                }
            }
        }

        return $rules;
    }
}

if (!function_exists('post_meta_value')) {
    function post_meta_value($post, string $key, $default = null)
    {
        if (!$post) {
            return $default;
        }

        if (!$post->relationLoaded('meta') && $post->exists) {
            $post->load('meta');
        }

        $meta = $post->meta->where('meta_key', $key)->first();

        return $meta?->meta_value ?? $default;
    }
}

if (!function_exists('post_meta_form_value')) {
    function post_meta_form_value($post, array $field)
    {
        $key = $field['key'] ?? null;
        if (!$key) {
            return null;
        }

        $value = post_meta_value($post, $key, $field['default'] ?? null);
        $type = $field['type'] ?? 'text';

        if (post_meta_is_json_type($type)) {
            $decoded = json_decode($value ?? '[]', true);
            return is_array($decoded) ? $decoded : [];
        }

        return $value;
    }
}

if (!function_exists('post_sync_meta')) {
    function post_sync_meta($post, array $metaFields, ?array $allowedKeys = null): void
    {
        if (!is_array($metaFields)) {
            $metaFields = [];
        }

        if ($allowedKeys !== null) {
            $metaFields = array_intersect_key($metaFields, array_flip($allowedKeys));
        }

        $existingMetaKeys = $post->meta()->pluck('meta_key')->toArray();

        foreach ($existingMetaKeys as $existingKey) {
            if (!array_key_exists($existingKey, $metaFields)) {
                $post->meta()->where('meta_key', $existingKey)->delete();
            }
        }

        foreach ($metaFields as $key => $value) {
            $value = is_array($value) ? json_encode($value) : $value;

            $existingMeta = $post->meta()->where('meta_key', $key)->first();
            if ($existingMeta) {
                $existingMeta->update(['meta_value' => $value]);
            } else {
                if ($value !== null && $value !== '') {
                    $post->meta()->create([
                        'meta_key' => $key,
                        'meta_value' => $value,
                    ]);
                }
            }
        }
    }
}

if (!function_exists('post_validation_rules')) {
    function post_validation_rules(string $layout, ?int $postId = null, ?int $companyId = null): array
    {
        $companyId = $companyId ?: auth()->user()?->company_id;

        $slugRule = Rule::unique('posts');
        if ($companyId) {
            $slugRule->where(function ($query) use ($companyId) {
                return $query->where('company_id', $companyId);
            });
        }
        if ($postId) {
            $slugRule->ignore($postId);
        }

        $rules = [
            'title' => 'required|string|min:3|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9][A-Za-z0-9\\-_.\\/]*$/',
                $slugRule,
                function ($attribute, $value, $fail) use ($companyId, $postId) {
                    if (slug_conflicts_with_pages_or_posts($value, $companyId, null, $postId)) {
                        $fail('Slug already exists in pages or posts.');
                    }
                },
            ],
            'language' => 'nullable|string|max:5',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'layout' => 'required|string|in:' . implode(',', array_keys(getPostLayouts())),
            'is_active' => 'required|boolean',
            'company_id' => 'required|exists:companies,id',
            'author_id' => 'required|exists:authors,id',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:255',
            'seo_schema' => 'nullable|string',
            'published_at' => 'nullable|date',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ];

        return array_merge($rules, getPostLayoutValidationRules($layout));
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
