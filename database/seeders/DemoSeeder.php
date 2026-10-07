<?php

namespace Database\Seeders;

use App\Enums\AwardPeriodType;
use App\Enums\CommentStatus;
use App\Enums\EnquiryForm;
use App\Enums\ManuscriptStage;
use App\Enums\PaymentPurpose;
use App\Models\AuthorCategory;
use App\Models\BestPaperAward;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogTag;
use App\Models\ContentCategory;
use App\Models\Enquiry;
use App\Models\JobPosting;
use App\Models\ManuscriptSubmission;
use App\Models\Payment;
use App\Models\PublicationCertificate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Local demo content so every screen has data. Not run in production.
 * Demo logins (password "password"): reviewer@moslegis.test, author@moslegis.test.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        self::ensureSampleManuscript();

        if (User::where('email', 'author@moslegis.test')->exists()) {
            return;
        }

        $contents = ContentCategory::all();
        $authorCategories = AuthorCategory::all();
        $admin = User::role('superadmin')->firstOrFail();

        $reviewer = User::factory()->reviewer($contents->take(4)->pluck('id')->all())
            ->create(['name' => 'Dr. Meera Raghavan', 'email' => 'reviewer@moslegis.test']);
        User::factory()->reviewer($contents->skip(4)->pluck('id')->all())
            ->create(['name' => 'Prof. Aditya Shenoy', 'email' => 'reviewer2@moslegis.test']);

        $author = User::factory()->author($authorCategories->firstWhere('name', 'LLM / PhD Scholar'))
            ->create(['name' => 'Ananya Iyer', 'email' => 'author@moslegis.test']);
        $authors = User::factory()->count(4)->author($authorCategories->random())->create()->push($author);

        $stages = [
            ManuscriptStage::Pending, ManuscriptStage::InReview, ManuscriptStage::Revision,
            ManuscriptStage::Approved, ManuscriptStage::Published, ManuscriptStage::Published,
            ManuscriptStage::Published, ManuscriptStage::Rejected, ManuscriptStage::Resubmitted,
        ];

        $published = collect();

        foreach ($stages as $i => $stage) {
            $owner = $authors[$i % $authors->count()];
            $content = $contents[$i % 4];
            $needsReviewer = ! in_array($stage, [ManuscriptStage::Pending, ManuscriptStage::Rejected], true);

            $submission = ManuscriptSubmission::factory()->stage($stage, $needsReviewer ? $reviewer : null)->create([
                'user_id' => $owner->id,
                'author_category_id' => $owner->authorProfile->author_category_id,
                'content_category_id' => $content->id,
                'word_count' => $content->min_word_limit + 500,
            ]);

            if ($stage !== ManuscriptStage::Pending) {
                Payment::factory()->paid()->create([
                    'user_id' => $owner->id,
                    'payable_id' => $submission->id,
                    'payment_purpose' => PaymentPurpose::Prescreening,
                ]);
            }

            if ($stage === ManuscriptStage::Published) {
                Payment::factory()->paid()->create([
                    'user_id' => $owner->id,
                    'payable_id' => $submission->id,
                    'payment_purpose' => PaymentPurpose::Publication,
                    'amount' => 2500,
                    'tax_amount' => 450,
                ]);
                PublicationCertificate::create([
                    'manuscript_submission_id' => $submission->id,
                    'certificate_number' => sprintf('MOS-CERT-%05d', $submission->id),
                    'document_path' => "certificates/demo-{$submission->id}.pdf",
                    'verification_slug' => Str::lower(Str::random(24)),
                    'issued_at' => $submission->published_at,
                ]);
                $published->push($submission);
            }
        }

        BestPaperAward::create([
            'manuscript_submission_id' => $published->first()->id,
            'period_type' => AwardPeriodType::Quarterly,
            'award_quarter' => BestPaperAward::lastQuarter()[0],
            'award_year' => BestPaperAward::lastQuarter()[1],
            'prize_amount' => 2000,
            'editorial_citation' => 'Selected by the editorial board for its careful doctrinal analysis and original contribution.',
            'selected_at' => now()->firstOfQuarter()->toDateString(),
            'selected_by' => $admin->id,
        ]);

        $tags = collect(['AI', 'Data Protection', 'Arbitration', 'Constitution', 'Criminal Procedure'])
            ->map(fn (string $name) => BlogTag::firstOrCreate(['slug' => Str::slug($name)], ['tag_name' => $name, 'user_id' => $admin->id]));

        Blog::factory()->count(8)->sequence(fn ($sequence) => [
            'user_id' => $sequence->index % 2 ? $author->id : $admin->id,
            'author_name' => $sequence->index % 2 ? $author->name : 'Editorial Board',
            'category_id' => BlogCategory::inRandomOrder()->value('id'),
            'featured_post' => $sequence->index === 0,
        ])->create()->each(function (Blog $blog) use ($tags, $author) {
            $blog->tags()->sync($tags->random(2)->pluck('id'));
            BlogComment::factory()->count(2)->create([
                'blog_id' => $blog->id,
                'user_id' => $author->id,
                'name' => $author->name,
                'email' => $author->email,
                'status' => CommentStatus::Approved,
            ]);
        });

        JobPosting::factory()->count(6)->create(['user_id' => $admin->id]);
        JobPosting::factory()->expired()->create(['user_id' => $author->id]);

        Enquiry::factory()->count(3)->create();
        Enquiry::factory()->create([
            'form_name' => EnquiryForm::Career,
            'phone' => '9876543210',
            'form_data' => ['position' => 'Editorial Assistant', 'resume_path' => null],
        ]);
    }
    /**
     * Demo manuscripts point at manuscripts/sample.docx (ManuscriptSubmissionFactory); create a small real
     * Word file there so archive downloads work with demo data.
     */
    public static function ensureSampleManuscript(): void
    {
        $path = 'manuscripts/sample.docx';
        if (Storage::disk('local')->exists($path)) {
            return;
        }

        $temp = tempnam(sys_get_temp_dir(), 'docx');
        $zip = new \ZipArchive;
        $zip->open($temp, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Sample manuscript (demo data).</w:t></w:r></w:p></w:body></w:document>');
        $zip->close();

        Storage::disk('local')->put($path, file_get_contents($temp));
        @unlink($temp);
    }
}
