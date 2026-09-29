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
