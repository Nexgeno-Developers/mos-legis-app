<?php

namespace App\Providers;

use App\Enums\RoleName;
use App\Models\ManuscriptSubmission;
use App\Models\PlagiarismCheck;
use App\Models\User;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\RazorpayGateway;
use App\Services\Payments\SimulatedGateway;
use App\Services\Plagiarism\FakePlagiarismChecker;
use App\Services\Plagiarism\OriginalityPlagiarismChecker;
use App\Services\Plagiarism\PlagiarismChecker;
use App\Socialite\OrcidProvider;
use App\Support\ActivityLogger;
use App\Support\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Settings::class);
        $this->app->singleton(ActivityLogger::class);

        // Razorpay when keys are configured; otherwise the simulated gateway (never in production).
        $this->app->bind(PaymentGateway::class, function () {
            $config = config('services.razorpay');

            if (filled($config['key_id']) && filled($config['key_secret'])) {
                return new RazorpayGateway($config['key_id'], $config['key_secret'], $config['webhook_secret']);
            }

            abort_if($this->app->isProduction(), 500, 'Razorpay keys are not configured.');

            return new SimulatedGateway;
        });

        // SOW A.18: PLAGIARISM_DRIVER=originality uses Originality.ai; anything else the simulated checker.
        $this->app->bind(PlagiarismChecker::class, fn () => match (config('services.plagiarism.driver')) {
            'originality' => new OriginalityPlagiarismChecker(config('services.plagiarism')),
            default => new FakePlagiarismChecker(config('services.plagiarism.fake_similarity')),
        });
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

        // SOW A.10: Superadmin has every permission by default. Limited to
        // permission names ("module.ability") so policy business rules
        // (e.g. "cannot delete yourself") still apply to superadmins.
        Gate::before(fn (User $user, string $ability) => str_contains($ability, '.') && $user->hasRole(RoleName::Superadmin->value) ? true : null);

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(8)->letters()->numbers()->uncompromised()
            : Password::min(8));

        Paginator::defaultView('components.pagination');

        // SOW B.01: ORCID sign-in (hardened driver, see App\Socialite\OrcidProvider).
        Event::listen(SocialiteWasCalled::class, fn (SocialiteWasCalled $event) => $event->extendSocialite('orcid', OrcidProvider::class));
    }
}
