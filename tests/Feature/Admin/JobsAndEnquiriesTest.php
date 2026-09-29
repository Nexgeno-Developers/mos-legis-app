<?php

namespace Tests\Feature\Admin;

use App\Enums\EnquiryForm;
use App\Enums\RecordStatus;
use App\Models\Enquiry;
use App\Models\JobPosting;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JobsAndEnquiriesTest extends TestCase
{
    private function jobPayload(array $overrides = []): array
    {
        return array_merge([
            'job_title' => 'Associate — Disputes',
            'organisation' => 'Khaitan & Co',
            'location' => 'Mumbai',
            'work_mode' => 'Hybrid',
            'employment_type' => 'Full time',
            'experience' => '2–4 years',
            'practice_area' => 'Litigation',
            'summary' => 'Summary',
            'responsibilities' => 'Responsibilities',
            'qualifications' => 'LL.B.',
            'required_skills' => 'Drafting',
            'application_method' => 'External URL',
            'application_email_url' => 'https://example.com/apply',
            'published_date' => today()->toDateString(),
            'application_deadline' => today()->addDays(10)->toDateString(),
            'expiry_date' => today()->addDays(30)->toDateString(),
            'source_name' => 'Firm website',
            'source_url' => 'https://example.com/careers',
            'status' => 'Active',
        ], $overrides);
    }

    #[Test]
    public function admin_creates_views_and_filters_job_postings(): void
    {
        $admin = $this->superadmin();

        $this->actingAs($admin)->post(route('admin.job-postings.store'), $this->jobPayload())
            ->assertRedirect(route('admin.job-postings.index'));
        $job = JobPosting::firstOrFail();
        $this->assertSame($admin->id, $job->user_id);

        JobPosting::factory()->expired()->create(['job_title' => 'Old Clerkship']);

        $this->actingAs($admin)->get(route('admin.job-postings.show', $job))->assertOk()->assertSee('Khaitan');
        $this->actingAs($admin)->get(route('admin.job-postings.index', ['status' => 'expired']))
            ->assertOk()->assertSee('Old Clerkship')->assertDontSee('Associate — Disputes');
        $this->actingAs($admin)->get(route('admin.job-postings.index', ['search' => 'Drafting']))
            ->assertOk()->assertSee('Associate — Disputes');
    }

    #[Test]
    public function application_email_must_match_the_method(): void
    {
        $this->actingAs($this->superadmin())->post(route('admin.job-postings.store'), $this->jobPayload([
            'application_method' => 'Email', 'application_email_url' => 'not-an-email',
        ]))->assertSessionHasErrors('application_email_url');
    }

    #[Test]
    public function expiry_cannot_precede_publication(): void
    {
        $this->actingAs($this->superadmin())->post(route('admin.job-postings.store'), $this->jobPayload([
            'expiry_date' => today()->subDay()->toDateString(),
        ]))->assertSessionHasErrors('expiry_date');
    }

    #[Test]
    public function job_can_be_toggled_and_deleted(): void
    {
        $admin = $this->superadmin();
        $job = JobPosting::factory()->create();

        $this->actingAs($admin)->patch(route('admin.job-postings.toggle-status', $job));
        $this->assertSame(RecordStatus::Inactive, $job->fresh()->status);

        $this->actingAs($admin)->delete(route('admin.job-postings.destroy', $job));
        $this->assertModelMissing($job);
    }

    #[Test]
    public function enquiries_filter_by_form_and_show_details(): void
    {
        $admin = $this->superadmin();
        $contact = Enquiry::factory()->create(['name' => 'Contact Person']);
        Enquiry::factory()->create(['name' => 'Career Applicant', 'form_name' => EnquiryForm::Career, 'form_data' => ['position' => 'Editor']]);

        $this->actingAs($admin)->get(route('admin.enquiries.index', ['form' => 'career']))
            ->assertOk()->assertSee('Career Applicant')->assertDontSee('Contact Person');
        $this->actingAs($admin)->get(route('admin.enquiries.show', $contact))->assertOk()->assertSee('Purpose');
    }

    #[Test]
    public function career_resume_is_downloadable_by_admins_only(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('resumes/cv.pdf', 'PDF');
        $enquiry = Enquiry::factory()->create(['form_name' => EnquiryForm::Career, 'form_data' => ['position' => 'Editor', 'resume_path' => 'resumes/cv.pdf']]);

        $this->actingAs($this->superadmin())->get(route('admin.enquiries.resume', $enquiry))->assertOk()->assertDownload();
        $this->actingAs($this->reviewer())->get(route('admin.enquiries.resume', $enquiry))->assertForbidden();
    }

    #[Test]
    public function enquiry_can_be_deleted(): void
    {
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($this->superadmin())->delete(route('admin.enquiries.destroy', $enquiry))->assertRedirect();

        $this->assertModelMissing($enquiry);
    }
}
