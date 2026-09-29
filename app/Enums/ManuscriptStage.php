<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * SOW A.16 manuscript pipeline.
 *
 * pending → plagiarism_accepted | rejected (plagiarism result)
 * plagiarism_accepted → in_review (reviewer assigned)
 * in_review | resubmitted → approved | revision | rejected (reviewer decision)
 * revision → resubmitted (author uploads a revised file)
 * approved → published (publication fee paid)
 */
enum ManuscriptStage: string
{
    use HasOptions;

    case Pending = 'pending';
    case PlagiarismAccepted = 'plagiarism_accepted';
    case Rejected = 'rejected';
    case InReview = 'in_review';
    case Revision = 'revision';
    case Resubmitted = 'resubmitted';
    case Approved = 'approved';
    case Published = 'published';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::PlagiarismAccepted, self::Rejected],
            self::PlagiarismAccepted => [self::InReview, self::Rejected],
            self::InReview, self::Resubmitted => [self::Approved, self::Revision, self::Rejected],
            self::Revision => [self::Resubmitted, self::Rejected],
            self::Approved => [self::Published],
            self::Rejected, self::Published => [],
        };
    }

    public function canTransitionTo(self $stage): bool
    {
        return in_array($stage, $this->allowedTransitions(), true);
    }

    /** Stages in which a manuscript counts as an active reviewer assignment. */
    public static function activeReview(): array
    {
        return [self::InReview, self::Revision, self::Resubmitted];
    }

    /** Stages a reviewer can decide on. */
    public function awaitsReviewerDecision(): bool
    {
        return in_array($this, [self::InReview, self::Resubmitted], true);
    }

    /** Badge tone used by the <x-badge> component. */
    public function tone(): string
    {
        return match ($this) {
            self::Pending => 'muted',
            self::PlagiarismAccepted, self::Resubmitted => 'info',
            self::InReview, self::Revision => 'warning',
            self::Approved, self::Published => 'success',
            self::Rejected => 'destructive',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PlagiarismAccepted => 'Plagiarism accepted',
            self::InReview => 'In review',
            default => ucfirst($this->value),
        };
    }
}
