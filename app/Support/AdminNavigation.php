<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Admin sidebar, grouped as in the wireframe (mos-legis-app admin-sidebar.tsx).
 * Items are hidden when the user lacks the module's view permission.
 */
final class AdminNavigation
{
    private const GROUPS = [
        'Overview' => [
            ['Dashboard', 'admin.dashboard', 'gauge', 'dashboard.view'],
        ],
        'Manuscripts' => [
            ['Submissions', 'admin.submissions.index', 'inbox', 'submissions.view'],
            ['Best Paper Awards', 'admin.best-paper-awards.index', 'award', 'submissions.best-paper'],
            ['Author Categories', 'admin.author-categories.index', 'square-user', 'author-categories.view'],
            ['Content Categories', 'admin.content-categories.index', 'book-open', 'content-categories.view'],
            ['Content Category Themes', 'admin.themes.index', 'calendar-range', 'themes.view'],
            ['Fees', 'admin.fees.index', 'badge-indian-rupee', 'fees.view'],
            ['Payments', 'admin.payments.index', 'credit-card', 'payments.view'],
            ['Plagiarism Checks', 'admin.plagiarism-checks.index', 'scan-search', 'plagiarism-checks.view'],
        ],
        'Content' => [
            ['Blogs', 'admin.blogs.index', 'file-text', 'blogs.view'],
            ['Blog Categories', 'admin.blog-categories.index', 'folder', 'blog-categories.view'],
            ['Blog Tags', 'admin.blog-tags.index', 'tags', 'blog-tags.view'],
            ['Blog Comments', 'admin.blog-comments.index', 'message-square', 'blog-comments.view'],
            ['Job Postings', 'admin.job-postings.index', 'briefcase', 'job-postings.view'],
            ['Pages', 'admin.pages.index', 'files', 'pages.view'],
            ['Menus', 'admin.menus.index', 'menu', 'menus.view'],
        ],
        'Operations' => [
            ['Enquiries', 'admin.enquiries.index', 'mail', 'enquiries.view'],
            ['Activity Logs', 'admin.activity-logs.index', 'activity', 'activity-logs.view'],
        ],
        'Administration' => [
            ['Users', 'admin.users.index', 'users', 'users.view'],
            ['Roles & Permissions', 'admin.roles.index', 'shield-check', 'roles.view'],
            ['Settings', 'admin.settings.edit', 'settings', 'settings.view'],
        ],
    ];

    /** @return array<string, list<array{label: string, route: string, icon: string, active: bool}>> */
    public static function for(User $user): array
    {
        $groups = [];

        foreach (self::GROUPS as $group => $items) {
            foreach ($items as [$label, $route, $icon, $permission]) {
                if (! $user->can($permission) || ! Route::has($route)) {
                    continue;
                }

                $prefix = preg_replace('/\.(index|edit)$/', '', $route);

                $groups[$group][] = [
                    'label' => $label,
                    'route' => $route,
                    'icon' => $icon,
                    'active' => request()->routeIs($route, $prefix.'.*'),
                ];
            }
        }

        return $groups;
    }

    public static function currentLabel(User $user): string
    {
        foreach (self::for($user) as $items) {
            foreach ($items as $item) {
                if ($item['active']) {
                    return $item['label'];
                }
            }
        }

        return request()->routeIs('admin.profile.*') ? 'Profile' : 'Dashboard';
    }
}
