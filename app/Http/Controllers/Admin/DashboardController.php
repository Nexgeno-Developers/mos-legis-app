<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BlogStatus;
use App\Enums\CommentStatus;
use App\Enums\ManuscriptStage;
use App\Enums\PaymentStatus;
use App\Enums\RecordStatus;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\JobPosting;
use App\Models\ManuscriptSubmission;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * SOW A.03 — summary cards, recent submissions and recent activity.
 * Each block is shown only when the user may view that module.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        Gate::authorize('dashboard.view');

        $user = $request->user();

        return view('admin.dashboard', [
            'users' => $user->can('users.view') ? $this->userStats() : null,
            'submissions' => $user->can('submissions.view') ? $this->submissionStats($user) : null,
            'payments' => $user->can('payments.view') ? $this->paymentStats() : null,
            'blogs' => $user->can('blogs.view') ? $this->blogStats() : null,
            'jobs' => $user->can('job-postings.view') ? $this->jobStats() : null,
            'recentSubmissions' => $user->can('submissions.view')
                ? ManuscriptSubmission::query()->visibleTo($user)
                    ->with(['author:id,name', 'reviewer:id,name', 'contentCategory:id,name'])
                    ->latest()->limit(8)->get()
                : collect(),
            'recentActivity' => $user->can('activity-logs.view')
                ? ActivityLog::with('user:id,name')->latest()->limit(8)->get()
                : collect(),
        ]);
    }

    private function userStats(): array
    {
        $counts = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', 'users')
            ->selectRaw('roles.name, COUNT(*) as total')
            ->groupBy('roles.name')
            ->pluck('total', 'name');

        return [
            'admins' => (int) ($counts[RoleName::Superadmin->value] ?? 0) + (int) ($counts[RoleName::Reviewer->value] ?? 0),
            'reviewers' => (int) ($counts[RoleName::Reviewer->value] ?? 0),
            'authors' => (int) ($counts[RoleName::Author->value] ?? 0),
        ];
    }

    private function submissionStats(User $user): array
    {
        $byStage = ManuscriptSubmission::query()->visibleTo($user)
            ->selectRaw('stage, COUNT(*) as total')->groupBy('stage')->pluck('total', 'stage');

        return [
            'total' => (int) $byStage->sum(),
            'pending' => (int) ($byStage[ManuscriptStage::Pending->value] ?? 0),
            'in_review' => (int) ($byStage[ManuscriptStage::InReview->value] ?? 0) + (int) ($byStage[ManuscriptStage::Resubmitted->value] ?? 0),
            'approved' => (int) ($byStage[ManuscriptStage::Approved->value] ?? 0),
            'published' => (int) ($byStage[ManuscriptStage::Published->value] ?? 0),
        ];
    }

    private function paymentStats(): array
    {
        $row = Payment::query()->where('payment_status', PaymentStatus::Paid)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as amount')->first();

        return ['count' => (int) $row->count, 'amount' => (float) $row->amount];
    }

    private function blogStats(): array
    {
        $blogs = Blog::query()->selectRaw('COUNT(*) as total, SUM(status = ?) as active', [BlogStatus::Published->value])->first();
        $comments = BlogComment::query()->selectRaw('COUNT(*) as total, SUM(status = ?) as approved, SUM(status = ?) as rejected', [
            CommentStatus::Approved->value, CommentStatus::Rejected->value,
        ])->first();

        return [
            'total' => (int) $blogs->total,
            'active' => (int) $blogs->active,
            'inactive' => (int) $blogs->total - (int) $blogs->active,
            'comments' => (int) $comments->total,
            'approved_comments' => (int) $comments->approved,
            'disapproved_comments' => (int) $comments->rejected,
        ];
    }

    private function jobStats(): array
    {
        $row = JobPosting::query()->selectRaw(
            'COUNT(*) as total, SUM(status = ? AND expiry_date >= ?) as active, SUM(expiry_date < ?) as expired',
            [RecordStatus::Active->value, today()->toDateString(), today()->toDateString()]
        )->first();

        return ['total' => (int) $row->total, 'active' => (int) $row->active, 'expired' => (int) $row->expired];
    }
}
