<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = BaseDataSeeder::class;

    /**
     * Tests wipe and re-migrate the database. Refuse to start unless it is a test database: a cached
     * config (php artisan optimize / config:cache) ignores phpunit.xml and would point at the real one.
     */
    public function createApplication()
    {
        $app = parent::createApplication();
        $connection = $app['config']->get('database.default');
        $database = (string) $app['config']->get("database.connections.{$connection}.database");

        if (! str_ends_with($database, '_test') && $database !== ':memory:') {
            throw new \RuntimeException("Refusing to run tests against the \"{$database}\" database. Run `php artisan config:clear` (a cached config ignores phpunit.xml) and make sure DB_DATABASE ends with _test.");
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function superadmin(array $attributes = []): User
    {
        return User::factory()->superadmin()->create($attributes);
    }

    /** @param  list<string>  $permissions */
    protected function reviewer(array $permissions = [], array $contentCategoryIds = []): User
    {
        $user = User::factory()->reviewer($contentCategoryIds)->create();

        if ($permissions) {
            $user->givePermissionTo($permissions);
        }

        return $user;
    }

    protected function author(array $attributes = []): User
    {
        return User::factory()->author()->create($attributes);
    }
}
