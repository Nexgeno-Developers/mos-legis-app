<?php

namespace Database\Factories;

use App\Enums\ManuscriptStage;
use App\Models\AuthorCategory;
use App\Models\ContentCategory;
use App\Models\ManuscriptSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManuscriptSubmission>
 */
class ManuscriptSubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'author_category_id' => AuthorCategory::factory(),
            'institution' => fake()->company().' University',
            'country' => 'India',
            'co_authors' => [fake()->name()],
            'title' => rtrim(fake()->sentence(8), '.'),
            'content_category_id' => ContentCategory::factory(),
            'word_count' => 4500,
            'keywords' => fake()->words(4),
            'abstract' => fake()->paragraph(4),
            'manuscript_attachment' => 'manuscripts/sample.docx',
            'is_original_unpublished_confirmed' => true,
            'is_coauthor_consent_confirmed' => true,
            'is_plagiarism_ai_declaration_confirmed' => true,
            'is_policies_accepted' => true,
            'is_prescreening_fee_terms_accepted' => true,
            'is_publication_fee_terms_accepted' => true,
        ];
    }

    public function stage(ManuscriptStage $stage, ?User $reviewer = null): static
    {
        return $this->state(fn () => array_filter([
            'stage' => $stage,
            'stage_changed_at' => now()->subDays(fake()->numberBetween(0, 20)),
            'assigned_to' => $reviewer?->id,
            'assigned_at' => $reviewer ? now()->subDays(10) : null,
            'plagiarism_similarity' => $stage === ManuscriptStage::Pending ? null : fake()->randomFloat(2, 1, 9.5),
            'published_at' => $stage === ManuscriptStage::Published ? now()->subDays(fake()->numberBetween(1, 200)) : null,
        ], fn ($value) => $value !== null))->afterMaking(function (ManuscriptSubmission $submission) use ($stage) {
            $submission->stage = $stage;
        });
    }
}
