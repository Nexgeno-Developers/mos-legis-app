<?php

namespace App\Models;

use App\Enums\ManuscriptStage;
use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * SOW A.16 — a manuscript moving through the pipeline described on ManuscriptStage.
 */
#[Fillable([
    'user_id', 'author_category_id', 'institution', 'country', 'co_authors',
    'title', 'content_category_id', 'content_category_theme_id', 'word_count', 'keywords', 'abstract',
    'manuscript_attachment',
    'is_original_unpublished_confirmed', 'is_coauthor_consent_confirmed',
    'is_plagiarism_ai_declaration_confirmed', 'is_policies_accepted',
    'is_prescreening_fee_terms_accepted', 'is_publication_fee_terms_accepted',
])]
class ManuscriptSubmission extends Model
{
    use HasFactory;

    public const DECLARATIONS = [
        'is_original_unpublished_confirmed' => 'The manuscript is original and has not been published or submitted elsewhere.',
        'is_coauthor_consent_confirmed' => 'All co-authors have consented to this submission.',
        'is_plagiarism_ai_declaration_confirmed' => 'I declare the work is free of plagiarism and have disclosed any use of AI tools.',
        'is_policies_accepted' => 'I accept the Author Guidelines, Peer Review Policy and Publication Ethics.',
        'is_prescreening_fee_terms_accepted' => 'I agree to pay the non-refundable plagiarism pre-screening fee.',
        'is_publication_fee_terms_accepted' => 'I agree to pay the publication fee if my manuscript is approved.',
    ];

    protected $attributes = [
        'stage' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'stage' => ManuscriptStage::class,
            'co_authors' => 'array',
            'keywords' => 'array',
            'plagiarism_similarity' => 'decimal:2',
            'word_count' => 'integer',
            'assigned_at' => 'datetime',
            'stage_changed_at' => 'datetime',
            'published_at' => 'datetime',
            'is_original_unpublished_confirmed' => 'boolean',
            'is_coauthor_consent_confirmed' => 'boolean',
            'is_plagiarism_ai_declaration_confirmed' => 'boolean',
            'is_policies_accepted' => 'boolean',
            'is_prescreening_fee_terms_accepted' => 'boolean',
            'is_publication_fee_terms_accepted' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function authorCategory(): BelongsTo
    {
        return $this->belongsTo(AuthorCategory::class, 'author_category_id');
    }

    public function contentCategory(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class, 'content_category_id');
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(ContentCategoryTheme::class, 'content_category_theme_id');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function prescreeningPayment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable')
            ->ofMany(['id' => 'max'], fn (Builder $q) => $q->where('payment_purpose', PaymentPurpose::Prescreening));
    }

    public function publicationPayment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable')
            ->ofMany(['id' => 'max'], fn (Builder $q) => $q->where('payment_purpose', PaymentPurpose::Publication));
    }

    public function plagiarismChecks(): HasMany
    {
        return $this->hasMany(PlagiarismCheck::class);
    }

    public function latestPlagiarismCheck(): HasOne
    {
        return $this->hasOne(PlagiarismCheck::class)->latestOfMany();
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ManuscriptRevision::class)->orderBy('round');
    }

    public function latestRevision(): HasOne
    {
        return $this->hasOne(ManuscriptRevision::class)->latestOfMany('round');
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(PublicationCertificate::class);
    }

    public function awards(): HasMany
    {
        return $this->hasMany(BestPaperAward::class);
    }

    /** Human reference shown in lists, e.g. MOS-00042. */
    public function reference(): string
    {
        return sprintf('MOS-%05d', $this->id);
    }

    /** Start of the current wait, for the "Waiting" column. */
    public function waitingSince(): Carbon
    {
        return $this->stage_changed_at ?? $this->created_at;
    }

    public function hasPaid(PaymentPurpose $purpose): bool
    {
        return $this->payments()
            ->where('payment_purpose', $purpose)
            ->where('payment_status', PaymentStatus::Paid)
            ->exists();
    }

    public function allDeclarationsAccepted(): bool
    {
        return collect(array_keys(self::DECLARATIONS))->every(fn (string $field) => (bool) $this->{$field});
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('stage', ManuscriptStage::Published);
    }

    #[Scope]
    protected function inStage(Builder $query, ManuscriptStage ...$stages): void
    {
        $query->whereIn('stage', $stages);
    }

    /** Reviewers only see their own assignments unless granted "submissions.view-all". */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if (! $user->can('submissions.view-all')) {
            $query->where('assigned_to', $user->id);
        }
    }
}
