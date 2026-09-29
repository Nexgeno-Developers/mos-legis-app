<?php

namespace App\Support;

/**
 * Admin-panel permission catalogue (SOW A.10). Superadmin bypasses every check
 * through Gate::before; Reviewers get whatever the superadmin grants.
 * Permission names are "<module>.<ability>".
 */
final class Permissions
{
    /** @var array<string, array{label: string, abilities: list<string>}> */
    public const MODULES = [
        'dashboard' => ['label' => 'Dashboard', 'abilities' => ['view']],
        'submissions' => ['label' => 'Manuscript Submissions', 'abilities' => ['view', 'view-all', 'create', 'edit', 'delete', 'assign', 'change-stage', 'review', 'best-paper']],
        'author-categories' => ['label' => 'Author Categories', 'abilities' => ['view', 'create', 'edit', 'delete']],
        'content-categories' => ['label' => 'Content Categories', 'abilities' => ['view', 'create', 'edit', 'delete']],
        'themes' => ['label' => 'Content Category Themes', 'abilities' => ['view', 'create', 'edit', 'delete']],
        'fees' => ['label' => 'Manuscript Fees', 'abilities' => ['view', 'edit']],
        'payments' => ['label' => 'Manuscript Payments', 'abilities' => ['view']],
        'plagiarism-checks' => ['label' => 'Plagiarism Checks', 'abilities' => ['view', 'recheck']],
        'blogs' => ['label' => 'Blogs', 'abilities' => ['view', 'create', 'edit', 'delete']],
        'blog-categories' => ['label' => 'Blog Categories', 'abilities' => ['view', 'create', 'edit', 'delete']],
        'blog-tags' => ['label' => 'Blog Tags', 'abilities' => ['view', 'create', 'edit', 'delete']],
        'blog-comments' => ['label' => 'Blog Comments', 'abilities' => ['view', 'create', 'edit', 'delete']],
        'job-postings' => ['label' => 'Job Postings', 'abilities' => ['view', 'create', 'edit', 'delete']],
        'pages' => ['label' => 'Pages', 'abilities' => ['view', 'create', 'edit', 'delete']],
        'enquiries' => ['label' => 'Enquiries', 'abilities' => ['view', 'delete']],
        'activity-logs' => ['label' => 'Activity Logs', 'abilities' => ['view', 'delete']],
        'users' => ['label' => 'Users', 'abilities' => ['view', 'create', 'edit', 'delete']],
        'roles' => ['label' => 'Roles & Permissions', 'abilities' => ['view', 'create', 'edit', 'delete']],
        'settings' => ['label' => 'Settings', 'abilities' => ['view', 'edit']],
    ];

    /** Seeded for the Reviewer role; the superadmin can change this from Roles & Permissions. */
    public const REVIEWER_DEFAULTS = ['dashboard.view', 'submissions.view', 'submissions.review'];

    /** @return list<string> */
    public static function all(): array
    {
        $names = [];

        foreach (self::MODULES as $module => $definition) {
            foreach ($definition['abilities'] as $ability) {
                $names[] = "{$module}.{$ability}";
            }
        }

        return $names;
    }
}
