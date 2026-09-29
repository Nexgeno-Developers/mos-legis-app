<?php

namespace Tests\Feature\Admin;

use App\Models\AuthorCategory;
use App\Models\ContentCategory;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    #[Test]
    public function superadmin_can_list_and_filter_users(): void
    {
        $admin = $this->superadmin();
        $author = $this->author(['name' => 'Ananya Iyer']);
        $this->reviewer();

        $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'Ananya']))
            ->assertOk()
            ->assertSee($author->email)
            ->assertDontSee('reviewer@');
    }

    #[Test]
    public function creating_a_reviewer_stores_content_categories(): void
    {
        $categories = ContentCategory::factory()->count(2)->create();

        $this->actingAs($this->superadmin())->post(route('admin.users.store'), [
            'name' => 'Dr. Meera',
            'email' => 'meera@moslegis.com',
            'status' => 'Active',
            'role' => 'reviewer',
            'password' => 'secret-pass-1',
            'password_confirmation' => 'secret-pass-1',
            'content_category_ids' => $categories->pluck('id')->all(),
        ])->assertRedirect(route('admin.users.index'));

        $reviewer = User::where('email', 'meera@moslegis.com')->firstOrFail();
        $this->assertTrue($reviewer->hasRole('reviewer'));
        $this->assertEqualsCanonicalizing($categories->pluck('id')->all(), $reviewer->reviewerContentCategories->pluck('id')->all());
    }

    #[Test]
    public function reviewer_requires_at_least_one_content_category(): void
    {
        $this->actingAs($this->superadmin())->post(route('admin.users.store'), [
            'name' => 'No Cats',
            'email' => 'nocats@moslegis.com',
            'status' => 'Active',
            'role' => 'reviewer',
            'password' => 'secret-pass-1',
            'password_confirmation' => 'secret-pass-1',
        ])->assertSessionHasErrors('content_category_ids');
    }

    #[Test]
    public function creating_an_author_stores_the_author_category(): void
    {
        $category = AuthorCategory::factory()->create();

        $this->actingAs($this->superadmin())->post(route('admin.users.store'), [
            'name' => 'Rahul',
            'email' => 'rahul@example.com',
            'status' => 'Active',
            'role' => 'author',
            'password' => 'secret-pass-1',
            'password_confirmation' => 'secret-pass-1',
            'author_category_id' => $category->id,
        ])->assertSessionHasNoErrors();

        $author = User::where('email', 'rahul@example.com')->firstOrFail();
        $this->assertSame($category->id, $author->authorProfile->author_category_id);
    }

    #[Test]
    public function changing_role_away_from_reviewer_clears_categories(): void
    {
        $category = ContentCategory::factory()->create();
        $reviewer = $this->reviewer([], [$category->id]);
        $authorCategory = AuthorCategory::factory()->create();

        $this->actingAs($this->superadmin())->put(route('admin.users.update', $reviewer), [
            'name' => $reviewer->name,
            'email' => $reviewer->email,
            'status' => 'Active',
            'role' => 'author',
            'author_category_id' => $authorCategory->id,
        ])->assertSessionHasNoErrors();

        $this->assertCount(0, $reviewer->fresh()->reviewerContentCategories);
        $this->assertTrue($reviewer->fresh()->hasRole('author'));
    }

    #[Test]
    public function user_can_be_deactivated(): void
    {
        $author = $this->author();

        $this->actingAs($this->superadmin())->patch(route('admin.users.toggle-status', $author))->assertRedirect();

        $this->assertFalse($author->fresh()->isActive());
    }

    #[Test]
    public function superadmin_cannot_delete_themselves(): void
    {
        $admin = $this->superadmin();
        $this->superadmin();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertForbidden();
    }

    #[Test]
    public function user_can_be_deleted(): void
    {
        $author = $this->author();

        $this->actingAs($this->superadmin())->delete(route('admin.users.destroy', $author))->assertRedirect();

        $this->assertModelMissing($author);
    }

    #[Test]
    public function reviewer_without_permission_cannot_manage_users(): void
    {
        $reviewer = $this->reviewer();

        $this->actingAs($reviewer)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($reviewer)->post(route('admin.users.store'), [])->assertForbidden();
    }

    #[Test]
    public function reviewer_granted_permission_can_view_users_but_not_create_superadmins(): void
    {
        $reviewer = $this->reviewer(['users.view', 'users.create']);

        $this->actingAs($reviewer)->get(route('admin.users.index'))->assertOk();

        $this->actingAs($reviewer)->post(route('admin.users.store'), [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'status' => 'Active',
            'role' => 'superadmin',
            'password' => 'secret-pass-1',
            'password_confirmation' => 'secret-pass-1',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'sneaky@example.com']);
    }
}
