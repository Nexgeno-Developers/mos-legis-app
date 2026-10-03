<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Editorial board: fixed sections on the public page and the section dropdown in the admin.
 */
class EditorialBoardSectionsTest extends TestCase
{
    private function board(array $members): Page
    {
        $page = Page::factory()->create(['title' => 'Editorial Board', 'slug' => 'editorial-board', 'template' => 'teams', 'status' => 'Published']);
        $page->metas()->create(['meta_key' => 'members', 'meta_value' => json_encode($members), 'meta_type' => 'json']);

        return $page;
    }

    #[Test]
    public function editorial_board_is_split_into_fixed_sections_in_order(): void
    {
        $this->board([
            ['name' => 'Prof. Advisor', 'designation' => 'Adviser', 'group' => 'advisory', 'overview' => ''],
            ['name' => 'Dr. Editor', 'designation' => 'Chief Editor', 'group' => 'board', 'overview' => ''],
            // Older free-text values still land in the right section.
            ['name' => 'The Founder', 'designation' => 'Founder', 'group' => 'Founder 1', 'overview' => ''],
        ]);

        $this->get(route('editorial-board'))->assertOk()->assertSeeInOrder([
            'Founders', 'Who Started the Review', 'The Founder',
            'Editorial Board', 'Board of Editors', 'Dr. Editor',
            'Advisory Board', 'Counsel to the Board', 'Prof. Advisor',
        ]);
    }

    #[Test]
    public function the_admin_team_editor_uses_a_section_dropdown_on_one_row(): void
    {
        $page = $this->board([['name' => 'A', 'designation' => 'B', 'group' => 'board', 'overview' => '']]);

        $this->actingAs($this->superadmin())->get(route('admin.pages.edit', $page))->assertOk()
            ->assertSee('md:grid-cols-3', false)->assertSee('<option value="founder">Founders</option>', false);
    }

    #[Test]
    public function section_labels_and_headings_are_edited_in_the_admin(): void
    {
        $page = $this->board([
            ['name' => 'Dr. Editor', 'designation' => 'Chief Editor', 'group' => 'board', 'overview' => ''],
            ['name' => 'Prof. Advisor', 'designation' => 'Adviser', 'group' => 'advisory', 'overview' => ''],
        ]);

        $this->actingAs($this->superadmin())->put(route('admin.pages.update', $page), [
            'title' => 'Editorial Board', 'slug' => 'editorial-board', 'status' => 'Published',
            'meta' => [
                'sections' => [
                    'board' => ['label' => 'Editors', 'heading' => 'The Editorial Team'],
                    // Emptied fields stay empty and are hidden on the site.
                    'advisory' => ['label' => '', 'heading' => ''],
                ],
                'members' => [
                    ['name' => 'Dr. Editor', 'designation' => 'Chief Editor', 'group' => 'board', 'overview' => ''],
                    ['name' => 'Prof. Advisor', 'designation' => 'Adviser', 'group' => 'advisory', 'overview' => ''],
                ],
            ],
        ])->assertSessionHasNoErrors();

        $this->get(route('editorial-board'))->assertOk()
            ->assertSeeInOrder(['Editors', 'The Editorial Team', 'Dr. Editor', 'Prof. Advisor'])
            ->assertDontSee('Board of Editors')->assertDontSee('Counsel to the Board')->assertDontSee('>Advisory Board<', false);

        // The admin's Section dropdown follows the renamed label.
        $this->actingAs($this->superadmin())->get(route('admin.pages.edit', $page))->assertOk()
            ->assertSee('<option value="board">Editors</option>', false)->assertSee('value="The Editorial Team"', false)
            // The dropdown still needs a name for the emptied section.
            ->assertSee('<option value="advisory">Advisory Board</option>', false);
    }
}
