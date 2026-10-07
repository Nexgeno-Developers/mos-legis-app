<?php

namespace App\Http\Controllers\Account;

use App\Enums\ManuscriptStage;
use App\Enums\PaymentStatus;
use App\Enums\PlagiarismCheckType;
use App\Models\PlagiarismCheck;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SOW B.03 — author dashboard.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $byStage = $user->submissions()->selectRaw('stage, COUNT(*) as total')->groupBy('stage')->pluck('total', 'stage');

        return view('account.dashboard', [
            'stats' => [
                'total' => (int) $byStage->sum(),
                'pending' => (int) ($byStage[ManuscriptStage::Pending->value] ?? 0),
                'in_review' => (int) collect(ManuscriptStage::activeReview())->sum(fn ($s) => $byStage[$s->value] ?? 0) + (int) ($byStage[ManuscriptStage::PlagiarismAccepted->value] ?? 0),
                'approved' => (int) ($byStage[ManuscriptStage::Approved->value] ?? 0),
                'published' => (int) ($byStage[ManuscriptStage::Published->value] ?? 0),
            ],
            'paid' => (float) $user->payments()->where('payment_status', PaymentStatus::Paid)->sum('total_amount'),
            'actionRequired' => $user->submissions()->whereIn('stage', [ManuscriptStage::Pending, ManuscriptStage::Revision, ManuscriptStage::Approved])
                ->with('prescreeningPayment')->latest('stage_changed_at')->get(),
            // Standalone checks created but never paid for (the check runs only after payment).
            'unpaidChecks' => PlagiarismCheck::where('user_id', $user->id)->where('check_type', PlagiarismCheckType::Standalone)
                ->whereDoesntHave('payment', fn ($q) => $q->where('payment_status', PaymentStatus::Paid))
                ->latest()->limit(5)->get(['id', 'title', 'created_at']),
            'recent' => $user->submissions()->with('contentCategory:id,name')->latest()->limit(5)->get(),
            'profileIncomplete' => ! $user->authorProfile?->author_category_id,
        ]);
    }
}
