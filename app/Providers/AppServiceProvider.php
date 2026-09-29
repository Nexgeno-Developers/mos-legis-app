<?php

namespace App\Providers;

use App\Enums\RoleName;
use App\Models\ManuscriptSubmission;
use App\Models\PlagiarismCheck;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Settings::class);
        $this->app->singleton(ActivityLogger::class);
    }

    public function boot(): void
    {
        // payments.payable_type stores table names (schema.sql), not class names.
        Relation::enforceMorphMap([
            'users' => User::class,
            'manuscript_submissions' => ManuscriptSubmission::class,
            'plagiarism_checks' => PlagiarismCheck::class,
        ]);

        Model::shouldBeStrict(! $this->app->isProduction());

        // SOW A.10: Superadmin has every permission by default.
        Gate::before(fn (User $user) => $user->hasRole(RoleName::Superadmin->value) ? true : null);

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(8)->letters()->numbers()->uncompromised()
            : Password::min(8));

        Paginator::defaultView('components.pagination');
    }
}
