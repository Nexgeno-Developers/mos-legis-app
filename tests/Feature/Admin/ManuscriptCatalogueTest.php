<?php

namespace Tests\Feature\Admin;

use App\Models\AuthorCategory;
use App\Models\ContentCategory;
use App\Models\ContentCategoryTheme;
use App\Models\ManuscriptFee;
use App\Models\ManuscriptSubmission;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ManuscriptCatalogueTest extends TestCase
{
    #[Test]
    public function catalogue_screens_render(): void
    {
        $admin = $this->superadmin();
        AuthorCategory::factory()->create(['name' => 'Advocate']);
        ContentCategory::factory()->create(['name' => 'Research Articles']);

        $this->actingAs($admin)->get(route('admin.author-categories.index'))->assertOk()->assertSee('Advocate');
        $this->actingAs($admin)->get(route('admin.content-categories.index'))->assertOk()->assertSee('Research Articles');
        $this->actingAs($admin)->get(route('admin.themes.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.fees.index'))->assertOk()->assertSee('Advocate')->assertSee('Research Articles');
    }

    #[Test]
    public function author_category_crud(): void
    {
        $admin = $this->superadmin();

        $this->actingAs($admin)->post(route('admin.author-categories.store'), ['name' => 'UG Student', 'status' => 'Active'])
            ->assertSessionHasNoErrors();
        $category = AuthorCategory::where('name', 'UG Student')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.author-categories.update', $category), ['name' => 'UG Student (Law)', 'status' => 'Inactive'])
            ->assertSessionHasNoErrors();
        $this->assertSame('UG Student (Law)', $category->fresh()->name);

        $this->actingAs($admin)->post(route('admin.author-categories.store'), ['name' => 'UG Student (Law)', 'status' => 'Active'])
            ->assertSessionHasErrors('name');

        $this->actingAs($admin)->delete(route('admin.author-categories.destroy', $category))->assertSessionHas('success');
        $this->assertModelMissing($category);
    }

    #[Test]
    public function category_in_use_cannot_be_deleted(): void
    {
        $submission = ManuscriptSubmission::factory()->create();

        $this->actingAs($this->superadmin())
            ->delete(route('admin.content-categories.destroy', $submission->content_category_id))
            ->assertSessionHas('error');

        $this->assertModelExists($submission->contentCategory);
    }

    #[Test]
    public function content_category_max_word_limit_must_not_be_below_min(): void
    {
        $this->actingAs($this->superadmin())->post(route('admin.content-categories.store'), [
            'name' => 'Notes', 'min_word_limit' => 3000, 'max_word_limit' => 1000, 'guideline' => 'x', 'status' => 'Active',
        ])->assertSessionHasErrors('max_word_limit');
    }

    #[Test]
    public function theme_period_is_stored_as_first_of_month(): void
    {
        $category = ContentCategory::factory()->create();

        $this->actingAs($this->superadmin())->post(route('admin.themes.store'), [
            'content_category_id' => $category->id, 'name' => 'AI in Law', 'volume' => 5, 'period' => '2026-09',
        ])->assertSessionHasNoErrors();

        $theme = ContentCategoryTheme::firstOrFail();
        $this->assertSame('2026-09-01', $theme->period->toDateString());
        $this->assertSame('September 2026', $theme->periodLabel());
    }

    #[Test]
    public function fee_matrix_saves_updates_and_clears_cells(): void
    {
        [$x, $y] = AuthorCategory::factory()->count(2)->create();
        [$a, $b] = ContentCategory::factory()->count(2)->create();
        ManuscriptFee::create(['author_category_id' => $y->id, 'content_category_id' => $b->id, 'fees' => 999]);

        $this->actingAs($this->superadmin())->put(route('admin.fees.update'), ['fees' => [
            $x->id => [$a->id => '100', $b->id => '120'],
            $y->id => [$a->id => '40', $b->id => ''],
        ]])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('manuscript_fees', ['author_category_id' => $x->id, 'content_category_id' => $a->id, 'fees' => 100]);
        $this->assertDatabaseHas('manuscript_fees', ['author_category_id' => $y->id, 'content_category_id' => $a->id, 'fees' => 40]);
        $this->assertDatabaseMissing('manuscript_fees', ['author_category_id' => $y->id, 'content_category_id' => $b->id]);
    }

    #[Test]
    public function fee_matrix_rejects_unknown_categories_and_negative_amounts(): void
    {
        $x = AuthorCategory::factory()->create();
        $a = ContentCategory::factory()->create();
        $admin = $this->superadmin();

        $this->actingAs($admin)->put(route('admin.fees.update'), ['fees' => [999 => [$a->id => '10']]])->assertSessionHasErrors('fees');
        $this->actingAs($admin)->put(route('admin.fees.update'), ['fees' => [$x->id => [$a->id => '-5']]])->assertSessionHasErrors();
    }

    #[Test]
    public function reviewer_without_permissions_is_forbidden(): void
    {
        $reviewer = $this->reviewer();

        $this->actingAs($reviewer)->get(route('admin.fees.index'))->assertForbidden();
        $this->actingAs($reviewer)->put(route('admin.fees.update'), ['fees' => []])->assertForbidden();
        $this->actingAs($reviewer)->post(route('admin.author-categories.store'), ['name' => 'x', 'status' => 'Active'])->assertForbidden();
    }
}
