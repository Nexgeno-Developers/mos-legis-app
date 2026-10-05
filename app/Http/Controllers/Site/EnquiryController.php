<?php

namespace App\Http\Controllers\Site;

use App\Enums\EnquiryForm;
use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Page;
use App\Notifications\WorkflowNotifier;
use App\Support\PhoneNumbers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SOW C.06 Contact and C.07 Careers pages with their forms (stored as enquiries, A.19).
 */
class EnquiryController extends Controller
{
    public const PURPOSES = ['Submission query', 'Review timeline', 'Payments & invoices', 'Patronage', 'Permissions & reprints', 'General query'];

    public function __construct(private readonly WorkflowNotifier $notifier) {}

    public function contact(Page $page): View
    {
        return view('site.contact', ['page' => $page, 'purposes' => self::PURPOSES]);
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $request->merge(['phone' => PhoneNumbers::normalize($request->input('phone'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => PhoneNumbers::rules(),
            'purpose' => ['required', 'string', 'in:'.implode(',', self::PURPOSES)],
            'message' => ['required', 'string', 'max:5000'],
            'website' => ['prohibited'], // honeypot
        ]);

        $enquiry = Enquiry::create([
            'form_name' => EnquiryForm::Contact,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'ip' => $request->ip(),
            'form_data' => ['purpose' => $data['purpose'], 'message' => $data['message']],
        ]);

        $this->notifyOffice($enquiry);

        return back()->with('success', 'Enquiry received — the editorial desk will reply by email.');
    }

    public function careers(Page $page): View
    {
        return view('site.careers', ['page' => $page]);
    }

    public function storeCareer(Request $request): RedirectResponse
    {
        $request->merge(['phone' => PhoneNumbers::normalize($request->input('phone'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => PhoneNumbers::rules(required: true),
            'position' => ['required', 'string', 'max:150'],
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'website' => ['prohibited'],
        ]);

        $enquiry = Enquiry::create([
            'form_name' => EnquiryForm::Career,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'ip' => $request->ip(),
            'form_data' => ['position' => $data['position'], 'resume_path' => $request->file('resume')->store('resumes', 'local')],
        ]);

        $this->notifyOffice($enquiry);

        return back()->with('success', 'Application received — thank you for your interest.');
    }

    private function notifyOffice(Enquiry $enquiry): void
    {
        $this->notifier->toEmail('enquiry_received', settings('general.application_email'), [
            'form' => $enquiry->form_name->value, 'name' => $enquiry->name, 'email' => $enquiry->email,
        ]);
    }
}
