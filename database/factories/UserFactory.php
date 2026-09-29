<?php

namespace Database\Factories;

use App\Enums\RecordStatus;
use App\Enums\RoleName;
use App\Models\AuthorCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '9'.fake()->numerify('#########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'status' => RecordStatus::Active,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => RecordStatus::Inactive]);
    }

    public function superadmin(): static
    {
        return $this->afterCreating(fn (User $user) => $user->assignRole(RoleName::Superadmin->value));
    }

    /** @param  list<int>  $contentCategoryIds */
    public function reviewer(array $contentCategoryIds = []): static
    {
        return $this->afterCreating(function (User $user) use ($contentCategoryIds) {
            $user->assignRole(RoleName::Reviewer->value);
            $user->reviewerContentCategories()->sync($contentCategoryIds);
        });
    }

    public function author(?AuthorCategory $category = null): static
    {
        return $this->afterCreating(function (User $user) use ($category) {
            $user->assignRole(RoleName::Author->value);
            $user->authorProfile()->create([
                'author_category_id' => $category?->id ?? AuthorCategory::factory()->create()->id,
                'institution' => fake()->company().' Law School',
                'country' => 'India',
            ]);
        });
    }
}
