<?php

namespace Tests\Feature\Admin;

use App\Models\AuthorCategory;
use App\Models\ContentCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Users create/edit: the data cards that belong to each role.
 */
class UserRoleDataTest extends TestCase
{
    private function base(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Asha Rao', 'email' => 'asha@example.com', 'status' => 'Active',
            'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1',
        ], $overrides);
    }

    #[Test]
    public function edit_page_shows_every_role_card(): void
    {
        $author = $this->author();

        $this->actingAs($this->superadmin())->get(route('admin.users.edit', $author))->assertOk()
            ->assertSee('Author profile')->assertSee('Billing address')
            ->assertSee('Review assignment')->assertSee('Additional permissions')->assertSee('Superadmin access');
    }

    #[Test]
    public function superadmin_manages_the_full_author_profile_and_billing_address(): void
    {
        Storage::fake('public');
        $category = AuthorCategory::factory()->create();

        $this->actingAs($this->superadmin())->post(route('admin.users.store'), $this->base([
            'role' => 'author',
            'author_category_id' => $category->id,
            'institution' => 'NLSIU Bengaluru',
            'country' => 'India',
            'orcid' => 'https://orcid.org/0000-0002-1825-009x',
            'bio' => 'Constitutional law scholar.',
            // 1×1 PNG (no GD needed).
            'profile_picture' => UploadedFile::fake()->createWithContent('asha.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==')),
            'address' => [
                'recipient_name' => 'Asha Rao', 'phone' => '98765 43210', 'address_line1' => '1 MG Road',
                'city' => 'Bengaluru', 'state' => 'Karnataka', 'country_code' => 'IN',
            ],
        ]))->assertSessionHasNoErrors()->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'asha@example.com')->firstOrFail();
        $profile = $user->authorProfile;
        $this->assertTrue($user->isAuthor());
        $this->assertSame('NLSIU Bengaluru', $profile->institution);
        $this->assertSame('0000-0002-1825-009X', $profile->orcid);
        $this->assertNotNull($profile->profile_picture);
        Storage::disk('public')->assertExists($profile->profile_picture);
        $this->assertSame('1 MG Road', $user->address->address_line1);
        $this->assertSame('+919876543210', $user->address->phone);
    }

    #[Test]
    public function billing_address_is_optional_but_must_be_complete_once_started(): void
    {
        $category = AuthorCategory::factory()->create();
        $admin = $this->superadmin();
        $data = $this->base(['role' => 'author', 'author_category_id' => $category->id]);

        $this->actingAs($admin)->post(route('admin.users.store'), $data + ['address' => ['country_code' => 'IN']])
            ->assertSessionHasNoErrors();
        $this->assertNull(User::where('email', 'asha@example.com')->first()->address);

        $this->actingAs($admin)->post(route('admin.users.store'), ['email' => 'second@example.com'] + $data + ['address' => ['city' => 'Pune', 'country_code' => 'IN']])
            ->assertSessionHasErrors(['address.recipient_name', 'address.address_line1']);
    }

    #[Test]
    public function an_orcid_already_used_by_another_author_is_rejected(): void
    {
        $this->author()->authorProfile()->update(['orcid' => '0000-0002-1825-0097']);
        $author = $this->author();

        $this->actingAs($this->superadmin())->put(route('admin.users.update', $author), [
            'name' => $author->name, 'email' => $author->email, 'status' => 'Active', 'role' => 'author',
            'author_category_id' => AuthorCategory::factory()->create()->id, 'orcid' => '0000-0002-1825-0097',
        ])->assertSessionHasErrors(['orcid' => 'This ORCID iD is already linked to another author.']);
    }

    #[Test]
    public function superadmin_grants_a_reviewer_extra_permissions_beyond_the_role(): void
    {
        $category = ContentCategory::factory()->create();
        $reviewer = $this->reviewer([], [$category->id]);

        $this->actingAs($this->superadmin())->put(route('admin.users.update', $reviewer), [
            'name' => $reviewer->name, 'email' => $reviewer->email, 'status' => 'Active', 'role' => 'reviewer',
            'content_category_ids' => [$category->id],
            // dashboard.view already comes with the Reviewer role and is not stored again.
            'permissions' => ['dashboard.view', 'blogs.view', 'blogs.edit'],
        ])->assertSessionHasNoErrors();

        $reviewer = $reviewer->fresh();
        $this->assertEqualsCanonicalizing(['blogs.view', 'blogs.edit'], $reviewer->getDirectPermissions()->pluck('name')->all());
        $this->actingAs($reviewer)->get(route('admin.blogs.index'))->assertOk();
    }

    #[Test]
    public function only_a_superadmin_can_grant_extra_permissions(): void
    {
        $category = ContentCategory::factory()->create();
        $manager = $this->reviewer(['users.view', 'users.edit']);
        $reviewer = $this->reviewer([], [$category->id]);

        $this->actingAs($manager)->put(route('admin.users.update', $reviewer), [
            'name' => $reviewer->name, 'email' => $reviewer->email, 'status' => 'Active', 'role' => 'reviewer',
            'content_category_ids' => [$category->id], 'permissions' => ['settings.edit'],
        ])->assertSessionHasNoErrors();

        $this->assertFalse($reviewer->fresh()->hasPermissionTo('settings.edit'));
    }

    #[Test]
    public function changing_a_user_to_superadmin_drops_extra_permissions(): void
    {
        $reviewer = $this->reviewer(['blogs.view']);

        $this->actingAs($this->superadmin())->put(route('admin.users.update', $reviewer), [
            'name' => $reviewer->name, 'email' => $reviewer->email, 'status' => 'Active', 'role' => 'superadmin',
        ])->assertSessionHasNoErrors();

        $this->assertCount(0, $reviewer->fresh()->getDirectPermissions());
        $this->assertTrue($reviewer->fresh()->isSuperadmin());
    }
}
