<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

/**
 * One editable template per workflow event (SOW A.16 "notified by email on
 * each event"; SOW Inclusions: email, WhatsApp and SMS). Placeholders in
 * {braces} are filled by App\Notifications\WorkflowNotifier.
 * Existing templates are never overwritten so admin edits survive re-seeding.
 */
class NotificationTemplateSeeder extends Seeder
{
    /** slug => [name, subject, body] */
    private const TEMPLATES = [
        'submission_received' => ['Submission received', 'Manuscript {reference} received',
            'Manuscript {reference} "{title}" has been received. The plagiarism pre-screening will start once the pre-screening fee is paid.'],
        // Audience-specific versions: what admins and reviewers need to know differs from the author's email.
        'submission_received_admin' => ['New manuscript (admin)', 'New manuscript {reference} submitted',
            'A new manuscript {reference} "{title}" was submitted by {author_name} under {content_category}. Plagiarism screening starts once the pre-screening fee is paid.'],
        'submission_in_review' => ['Under peer review (author)', 'Manuscript {reference} is under peer review',
            'Manuscript {reference} "{title}" passed screening and has been sent for double-blind peer review. We will email you as soon as the reviewer reaches a decision.'],
        'submission_assigned_reviewer' => ['Assigned to you (reviewer)', 'Manuscript {reference} assigned to you for review',
            'Manuscript {reference} "{title}" ({content_category}) has been assigned to you for review. Please sign in to the admin panel to read it and record your decision.'],
        'submission_reassigned_reviewer' => ['Reassigned (previous reviewer)', 'Manuscript {reference} reassigned',
            'Manuscript {reference} "{title}" has been reassigned to another reviewer. No further action is needed from you.'],
        'submission_approved_staff' => ['Manuscript approved (staff)', 'Manuscript {reference} approved',
            'Manuscript {reference} "{title}" has been approved. It will be published once the author pays the publication fee of {amount}.'],
        'submission_published_staff' => ['Manuscript published (staff)', 'Manuscript {reference} published',
            'Manuscript {reference} "{title}" by {author_name} is now published and listed in the Journal Archive.'],
        'payment_received_admin' => ['Payment received (admin)', 'Payment received from {payer_name} — {invoice_number}',
            'A payment of {amount} for {purpose} was received from {payer_name} ({payer_email}). Invoice {invoice_number}.'],
        'submission_plagiarism_accepted' => ['Plagiarism check passed', 'Manuscript {reference} passed plagiarism screening',
            'Manuscript {reference} "{title}" passed plagiarism screening with {similarity}% similarity and will now be sent for review.'],
        'submission_plagiarism_rejected' => ['Plagiarism check failed', 'Manuscript {reference} rejected at plagiarism screening',
            'Manuscript {reference} "{title}" returned {similarity}% similarity, above the {threshold}% limit, and has been rejected.'],
        'submission_assigned' => ['Reviewer assigned', 'Manuscript {reference} assigned for review',
            'Manuscript {reference} "{title}" has been assigned to {reviewer_name} for review.'],
        'submission_unassigned' => ['No reviewer available', 'Manuscript {reference} is waiting for a reviewer',
            'No active reviewer covers "{content_category}". Manuscript {reference} "{title}" is pending assignment — please assign a reviewer manually.'],
        'submission_revision_requested' => ['Revision requested', 'Revision requested for manuscript {reference}',
            'The reviewer has requested a revision of manuscript {reference} "{title}". Remarks: {remarks}'],
        'submission_resubmitted' => ['Manuscript resubmitted', 'Manuscript {reference} resubmitted',
            'The author has resubmitted manuscript {reference} "{title}" for review.'],
        'submission_approved' => ['Manuscript approved', 'Manuscript {reference} approved',
            'Manuscript {reference} "{title}" has been approved. The publication fee of {amount} is now payable from your account.'],
        'submission_rejected' => ['Manuscript rejected', 'Manuscript {reference} rejected',
            'Manuscript {reference} "{title}" has been rejected. Remarks: {remarks}'],
        'submission_published' => ['Manuscript published', 'Manuscript {reference} published',
            'Manuscript {reference} "{title}" is now published. The publication certificate is available from your account.'],
        'submission_stage_changed' => ['Stage changed', 'Manuscript {reference} is now {stage}',
            'The stage of manuscript {reference} "{title}" was changed to {stage}.'],
        'payment_received' => ['Payment received', 'Payment received — {invoice_number}',
            'We have received your payment of {amount} for {purpose}. Invoice {invoice_number} is available from your account.'],
        'plagiarism_check_completed' => ['Plagiarism check completed', 'Your plagiarism check is ready',
            'Your plagiarism check "{title}" is complete with {similarity}% similarity. The full report is available from your account.'],
        'best_paper_selected' => ['Best Paper winner', 'Congratulations — Best Paper {period}',
            'Manuscript {reference} "{title}" has been selected as the Best Paper for {period}.'],
        'blog_pending_approval' => ['Blog awaiting approval', 'Blog post awaiting approval',
            'The blog post "{title}" by {author_name} is awaiting approval.'],
        'blog_approved' => ['Blog approved', 'Your blog post is live',
            'Your blog post "{title}" has been approved and is now published.'],
        'job_pending_approval' => ['Job awaiting approval', 'Job posting awaiting approval',
            'The job posting "{title}" by {author_name} is awaiting approval.'],
        'job_approved' => ['Job approved', 'Your job posting is live',
            'Your job posting "{title}" has been approved and is now listed on the job board.'],
        'enquiry_received' => ['Enquiry received', 'New {form} enquiry from {name}',
            'A new {form} form submission was received from {name} ({email}).'],
    ];

    public function run(): void
    {
        foreach (self::TEMPLATES as $slug => [$name, $subject, $body]) {
            NotificationTemplate::firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'email_subject' => $subject,
                'email_template' => '<p>Dear {recipient_name},</p><p>'.$body.'</p><p>— MOS Legis Editorial Office</p>',
                'sms_template' => 'MOS Legis: '.$body,
                // WATI expects the approved WhatsApp template name here.
                'whatsapp_template' => $slug,
                'email_enabled' => true,
                'sms_enabled' => true,
                'whatsapp_enabled' => true,
                'status' => true,
            ]);
        }
    }
}
