<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnquiryForm;
use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * SOW A.19 — Career & Contact form submissions. The Career/Contact buttons only filter the list.
 */
class EnquiryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:enquiries.view', only: ['index', 'show', 'resume']),
            new Middleware('can:enquiries.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $enquiries = Enquiry::query()
            ->when($request->enum('form', EnquiryForm::class), fn ($q, $form) => $q->where('form_name', $form))
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('form_data', 'like', "%{$search}%")))
            ->when($request->date('from'), fn ($q, $from) => $q->where('created_at', '>=', $from->startOfDay()))
            ->when($request->date('to'), fn ($q, $to) => $q->where('created_at', '<=', $to->endOfDay()))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = Enquiry::query()->selectRaw('form_name, COUNT(*) as total')->groupBy('form_name')->pluck('total', 'form_name');

        return view('admin.enquiries.index', compact('enquiries', 'counts'));
    }

    public function show(Enquiry $enquiry): View
    {
        return view('admin.enquiries.show', compact('enquiry'));
    }

    /** Career CVs are stored on the private disk and streamed to authorised admins only. */
    public function resume(Enquiry $enquiry): StreamedResponse
    {
        $path = $enquiry->form_data['resume_path'] ?? null;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, 'CV-'.str($enquiry->name)->slug().'.'.pathinfo($path, PATHINFO_EXTENSION));
    }

    public function destroy(Enquiry $enquiry): RedirectResponse
    {
        if ($path = $enquiry->form_data['resume_path'] ?? null) {
            Storage::disk('local')->delete($path);
        }

        activity()->log('Enquiries', 'Deleted enquiry', $enquiry, ['form' => $enquiry->form_name->value, 'email' => $enquiry->email]);
        $enquiry->delete();

        return redirect()->route('admin.enquiries.index')->with('success', 'Enquiry deleted.');
    }
}
