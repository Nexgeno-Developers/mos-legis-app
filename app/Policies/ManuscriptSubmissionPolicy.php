<?php

namespace App\Policies;

use App\Enums\PaymentStatus;
use App\Models\ManuscriptSubmission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Admin access follows the permissions the superadmin grants (reviewers see
 * their own assignments unless given "submissions.view-all"); authors see
 * only their own manuscripts from the portal.
 */
class ManuscriptSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('submissions.view');
    }

    public function view(User $user, ManuscriptSubmission $submission): bool
    {
        if ($user->isAuthor()) {
            return $submission->user_id === $user->id;
        }

        return $user->can('submissions.view')
            && ($user->can('submissions.view-all') || $submission->assigned_to === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->can('submissions.create');
    }

    public function update(User $user, ManuscriptSubmission $submission): bool
    {
        return $user->can('submissions.edit') && $this->view($user, $submission);
    }

    public function delete(User $user, ManuscriptSubmission $submission): Response
    {
        if (! $user->can('submissions.delete')) {
            return Response::deny();
        }

        return $submission->payments()->where('payment_status', PaymentStatus::Paid)->exists()
            ? Response::deny('Manuscripts with paid fees cannot be deleted — reject them instead.')
            : Response::allow();
    }

    public function assign(User $user, ManuscriptSubmission $submission): bool
    {
        return $user->can('submissions.assign');
    }

    public function changeStage(User $user, ManuscriptSubmission $submission): bool
    {
        return $user->can('submissions.change-stage');
    }

    /** The assigned reviewer decides; users with "view-all" (e.g. superadmin) may decide on any manuscript. */
    public function review(User $user, ManuscriptSubmission $submission): bool
    {
        return $user->can('submissions.review')
            && ($submission->assigned_to === $user->id || $user->can('submissions.view-all'));
    }

    public function awardBestPaper(User $user, ManuscriptSubmission $submission): bool
    {
        return $user->can('submissions.best-paper');
    }

    /** Author actions from the portal. */
    public function resubmit(User $user, ManuscriptSubmission $submission): bool
    {
        return $submission->user_id === $user->id;
    }

    public function pay(User $user, ManuscriptSubmission $submission): bool
    {
        return $submission->user_id === $user->id;
    }
}
