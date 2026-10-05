<?php

namespace Tests\Feature\Site;

use App\Enums\BlogStatus;
use App\Enums\ManuscriptStage;
use App\Models\Blog;
use App\Models\Enquiry;
use App\Models\JobPosting;
use App\Models\ManuscriptSubmission;
use App\Models\PublicationCertificate;
use Database\Seeders\CatalogueSeeder;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Docx;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CatalogueSeeder::class, PageSeeder::class, NotificationTemplateSeeder::class]);
    }

    #[Test]
    public function public_pages_render(): void
    {
        foreach (['home', 'archive.index', 'blogs.index', 'login', 'register'] as $route) {
            $this->get(route($route))->assertOk();
        }

        // CMS pages at their seeded slugs.
        foreach (['about', 'editorial-board', 'patrons', 'submit', 'best-paper', 'job-postings', 'contact', 'careers', 'plagiarism-checker', 'privacy-policy'] as $slug) {
            $this->get('/'.$slug)->assertOk();
        }

        $this->get(url('privacy-policy'))->assertOk()->assertSee('Privacy Policy');
        $this->get('/no-such-page')->assertNotFound();
        $this->get(page_url('teams'))->assertSee('Vishnu Yadav');
        $this->get(page_url('contact'))->assertSee('chiefeditor@moslegis.com');
    }

    #[Test]
    public function archive_lists_only_published_and_filters(): void
    {
        Storage::fake('local');
        $published = ManuscriptSubmission::factory()->stage(ManuscriptStage::Published)->create([
            'title' => 'Published Federalism Paper', 'manuscript_attachment' => Docx::withWords(10)->store('manuscripts', 'local'),
        ]);
        ManuscriptSubmission::factory()->stage(ManuscriptStage::InReview)->create(['title' => 'Secret Draft']);

        $this->get(route('archive.index'))->assertOk()->assertSee('Published Federalism Paper')->assertDontSee('Secret Draft');
        $this->get(route('archive.index', ['title' => 'nothing-matches']))->assertDontSee('Published Federalism Paper');
        $this->get(route('archive.show', $published))->assertOk();
        $this->get(route('archive.download', $published))->assertOk()->assertDownload();
        $this->get(route('archive.zip'))->assertOk()->assertDownload();

        $draft = ManuscriptSubmission::where('title', 'Secret Draft')->first();
        $this->get(route('archive.show', $draft))->assertNotFound();
        $this->get(route('archive.download', $draft))->assertNotFound();
    }

    #[Test]
    public function blog_detail_counts_views_and_accepts_comments_from_signed_in_users_only(): void
    {
        $blog = Blog::factory()->create(['status' => BlogStatus::Published]);
        $draft = Blog::factory()->draft()->create();

        $this->get(route('blogs.show', $blog->slug))->assertOk();
        $this->get(route('blogs.show', $blog->slug));
        $this->assertSame(1, $blog->fresh()->views);
        $this->get(route('blogs.show', $draft->slug))->assertNotFound();

        $this->post(route('blogs.comments.store', $blog->slug), ['comment' => 'Hello there'])->assertRedirect(route('login'));
        $this->actingAs($this->author())->post(route('blogs.comments.store', $blog->slug), ['comment' => 'Great analysis'])->assertSessionHas('success');
        $this->assertDatabaseHas('blog_comments', ['blog_id' => $blog->id, 'status' => 'Pending']);
        $this->get(route('blogs.show', $blog->slug))->assertDontSee('Great analysis');
    }

    #[Test]
    public function jobs_page_shows_only_live_listings(): void
    {
        JobPosting::factory()->create(['job_title' => 'Live Associate']);
        JobPosting::factory()->expired()->create(['job_title' => 'Expired Clerk']);
        JobPosting::factory()->create(['job_title' => 'Hidden Role', 'status' => 'Inactive']);

        $this->get(page_url('jobs'))->assertOk()->assertSee('Live Associate')->assertDontSee('Expired Clerk')->assertDontSee('Hidden Role');
    }

    #[Test]
    public function contact_and_career_forms_create_enquiries(): void
    {
        Storage::fake('local');
        Mail::fake();

        $this->post(route('contact.store'), [
            'name' => 'Visitor', 'email' => 'v@example.com', 'phone' => '98765 43210', 'purpose' => 'General query', 'message' => 'Hello',
        ])->assertSessionHas('success');

        // Phone is stored with its country code; the form no longer asks for a submission ID.
        $contact = Enquiry::where('form_name', 'contact')->firstOrFail();
        $this->assertSame('+919876543210', $contact->phone);
        $this->assertArrayNotHasKey('submission_id', $contact->form_data);
        $this->get(page_url('contact'))->assertSee('data-phone="phone"', false)->assertDontSee('Submission ID');

        $this->post(route('careers.store'), [
            'name' => 'Applicant', 'email' => 'a@example.com', 'phone' => '9876543210', 'position' => 'Editorial Assistant',
            'resume' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ])->assertSessionHas('success');

        $this->assertSame(1, Enquiry::where('form_name', 'contact')->count());
        $career = Enquiry::where('form_name', 'career')->firstOrFail();
        Storage::disk('local')->assertExists($career->form_data['resume_path']);

        $this->post(route('contact.store'), ['name' => 'Bot', 'email' => 'b@example.com', 'purpose' => 'General query', 'message' => 'x', 'website' => 'spam'])
            ->assertSessionHasErrors('website');
    }

    #[Test]
    public function certificate_verification(): void
    {
        $submission = ManuscriptSubmission::factory()->stage(ManuscriptStage::Published)->create();
        $certificate = PublicationCertificate::create([
            'manuscript_submission_id' => $submission->id, 'certificate_number' => 'MOS-CERT-2026-00001',
            'document_path' => 'certificates/x.pdf', 'verification_slug' => 'abc123', 'issued_at' => now(),
            'snapshot_json' => ['title' => 'Certified Title', 'author' => 'A', 'reference' => 'MOS-00001'],
        ]);

        $this->get(route('certificates.verify', $certificate->verification_slug))->assertOk()->assertSee('Certified Title');
        $this->get(route('certificates.verify', 'nope'))->assertOk()->assertSee('No certificate matches');
    }

    #[Test]
    public function guests_see_sign_in_prompt_on_submit_page(): void
    {
        $this->get(page_url('submit'))->assertOk()->assertSee('Sign in to submit')->assertSee('Research Articles');
    }
}
