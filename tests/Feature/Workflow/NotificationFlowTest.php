<?php

namespace Tests\Feature\Workflow;

use App\Enums\ManuscriptStage;
use App\Enums\RevisionDecision;
use App\Models\ContentCategory;
use App\Models\ManuscriptSubmission;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\Manuscripts\ManuscriptWorkflow;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Docx;
use Tests\TestCase;

/**
 * Each audience (author, assigned reviewer, admins) gets the email meant for it at every stage.
 */
class NotificationFlowTest extends TestCase
{
    private User $admin;

    private ContentCategory $content;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();
        $this->seed(NotificationTemplateSeeder::class);
        $this->admin = $this->superadmin();
        $this->content = ContentCategory::factory()->create();
    }

    /** Email templates sent to an address. */
    private function sentTo(User $user): array
    {
        return NotificationLog::where('recipient', $user->email)->where('channel', 'email')->pluck('template_slug')->all();
    }

    private function submission(ManuscriptStage $stage = ManuscriptStage::PlagiarismAccepted): ManuscriptSubmission
    {
        return ManuscriptSubmission::factory()->stage($stage)->create([
            'content_category_id' => $this->content->id,
            'manuscript_attachment' => Docx::withWords(300)->store('manuscripts', 'local'),
        ]);
    }

    #[Test]
    public function on_assignment_the_author_never_learns_the_reviewer_and_each_party_gets_its_own_email(): void
    {
        $submission = $this->submission();
        $reviewer = $this->reviewer([], [$this->content->id]);

        app(ManuscriptWorkflow::class)->assign($submission, $reviewer, $this->admin);

        $this->assertContains('submission_in_review', $this->sentTo($submission->author));
        $this->assertNotContains('submission_assigned', $this->sentTo($submission->author));
        $this->assertFalse(NotificationLog::where('recipient', $submission->author->email)->where('message', 'like', '%'.$reviewer->name.'%')->exists());

        $this->assertContains('submission_assigned_reviewer', $this->sentTo($reviewer));
        $this->assertContains('submission_assigned', $this->sentTo($this->admin));
    }

    #[Test]
    public function a_replaced_reviewer_is_told_and_the_author_is_not_emailed_again(): void
    {
        $submission = $this->submission();
        $first = $this->reviewer([], [$this->content->id]);
        $second = $this->reviewer([], [$this->content->id]);
        $workflow = app(ManuscriptWorkflow::class);

        $workflow->assign($submission, $first, $this->admin);
        $workflow->assign($submission->fresh(), $second, $this->admin);

        $this->assertContains('submission_reassigned_reviewer', $this->sentTo($first));
        $this->assertContains('submission_assigned_reviewer', $this->sentTo($second));
        $this->assertSame(1, collect($this->sentTo($submission->author))->filter(fn ($t) => $t === 'submission_in_review')->count());
    }

    #[Test]
    public function decisions_notify_author_and_admins_but_not_the_deciding_reviewer(): void
    {
        $submission = $this->submission();
        $reviewer = $this->reviewer([], [$this->content->id]);
        $workflow = app(ManuscriptWorkflow::class);
        $workflow->assign($submission, $reviewer, $this->admin);

        $workflow->decide($submission->fresh(), $reviewer, RevisionDecision::Revision, 'Expand part II.');

        $this->assertContains('submission_revision_requested', $this->sentTo($submission->author));
        $this->assertContains('submission_revision_requested', $this->sentTo($this->admin));
        $this->assertNotContains('submission_revision_requested', $this->sentTo($reviewer));

        // The author resubmits: reviewer and admins are told, the author isn't.
        $workflow->resubmit($submission->fresh(), 'manuscripts/revised.docx', 1200, 'Done.');
        $this->assertContains('submission_resubmitted', $this->sentTo($reviewer));
        $this->assertContains('submission_resubmitted', $this->sentTo($this->admin));
        $this->assertNotContains('submission_resubmitted', $this->sentTo($submission->author));
    }

    #[Test]
    public function a_new_submission_and_approval_use_staff_wording_for_admins(): void
    {
        $submission = $this->submission(ManuscriptStage::Pending);
        app(ManuscriptWorkflow::class)->submitted($submission);

        $this->assertContains('submission_received', $this->sentTo($submission->author));
        $this->assertContains('submission_received_admin', $this->sentTo($this->admin));
        $this->assertNotContains('submission_received', $this->sentTo($this->admin));
    }
}
