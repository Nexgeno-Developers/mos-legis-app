<?php

namespace App\Support;

/**
 * Every setting the admin Settings screen exposes (SOW A.21), with its type
 * and default. Defaults mirror the seed rows in schema.sql.
 */
final class SettingsRegistry
{
    /** @var array<string, array{label: string, fields: array<string, array{label: string, type: string, default: string, options?: list<string>}>}> */
    public const GROUPS = [
        'general' => [
            'label' => 'General',
            'fields' => [
                'application_name' => ['label' => 'Application Name', 'type' => 'text', 'default' => 'MOS Legis'],
                'application_logo' => ['label' => 'Application Logo', 'type' => 'image', 'default' => ''],
                'favicon' => ['label' => 'Favicon', 'type' => 'image', 'default' => ''],
                'application_email' => ['label' => 'Application Email', 'type' => 'email', 'default' => 'admin@moslegis.com'],
                'contact_number' => ['label' => 'Contact Number', 'type' => 'text', 'default' => ''],
                'address' => ['label' => 'Address', 'type' => 'textarea', 'default' => ''],
                'default_country' => ['label' => 'Default Country', 'type' => 'text', 'default' => 'India'],
                'default_timezone' => ['label' => 'Default Timezone', 'type' => 'timezone', 'default' => 'Asia/Kolkata'],
                'date_format' => ['label' => 'Date Format', 'type' => 'select', 'default' => 'DD-MM-YYYY', 'options' => ['DD-MM-YYYY', 'MM-DD-YYYY', 'YYYY-MM-DD', 'DD MMM YYYY']],
            ],
        ],
        // What authors and readers publish on the website: on = an admin approves it first, off = it goes live at once.
        'approvals' => [
            'label' => 'Approvals',
            'fields' => [
                'blog_author_approval_required' => ['label' => 'Blog Posts Approval Required (author posts are reviewed before they go live)', 'type' => 'boolean', 'default' => '1'],
                'blog_comment_approval_required' => ['label' => 'Blog Comment Approval Required (comments appear after an admin approves them)', 'type' => 'boolean', 'default' => '1'],
                'job_author_approval_required' => ['label' => 'Job Posting Approval Required (author job postings are listed after an admin approves them)', 'type' => 'boolean', 'default' => '1'],
            ],
        ],
        'manuscript' => [
            'label' => 'Manuscript',
            'fields' => [
                'plagiarism_prescreening_fee' => ['label' => 'Plagiarism Pre-Screening Fee (₹)', 'type' => 'number', 'default' => '150'],
                'plagiarism_max_similarity_percent' => ['label' => 'Plagiarism Maximum Similarity Percentage', 'type' => 'number', 'default' => '10'],
                'manuscript_certificate_enabled' => ['label' => 'Manuscript Certificate', 'type' => 'boolean', 'default' => '1'],
                'auto_assign_reviewer_enabled' => ['label' => 'Auto Assign Reviewer', 'type' => 'boolean', 'default' => '1'],
                'manuscript_email_notifications_enabled' => ['label' => 'Manuscript Email Notifications', 'type' => 'boolean', 'default' => '1'],
            ],
        ],
        'payment' => [
            'label' => 'Payment',
            'fields' => [
                'currency' => ['label' => 'Currency', 'type' => 'select', 'default' => 'INR', 'options' => ['INR']],
                'currency_symbol' => ['label' => 'Currency Symbol', 'type' => 'text', 'default' => '₹'],
                'payment_gateway' => ['label' => 'Payment Gateway', 'type' => 'select', 'default' => 'Razorpay', 'options' => ['Razorpay']],
                'payment_gateway_mode' => ['label' => 'Payment Gateway Mode', 'type' => 'select', 'default' => 'Test', 'options' => ['Test', 'Live']],
                'payment_enabled' => ['label' => 'Payments Enabled', 'type' => 'boolean', 'default' => '1'],
                'tax_rate_percent' => ['label' => 'Tax Rate Percentage (Indian payers, included in all fees)', 'type' => 'number', 'default' => '18'],
                'business_state' => ['label' => 'Business State (buyers here: CGST + SGST; other Indian states: IGST)', 'type' => 'text', 'default' => 'Maharashtra'],
            ],
        ],
        'seo_social' => [
            'label' => 'SEO & Social',
            'fields' => [
                'default_meta_title' => ['label' => 'Default Meta Title', 'type' => 'text', 'default' => 'MOS Legis'],
                'default_meta_description' => ['label' => 'Default Meta Description', 'type' => 'textarea', 'default' => ''],
                'default_og_image' => ['label' => 'Default OG Image', 'type' => 'image', 'default' => ''],
                'facebook_url' => ['label' => 'Facebook URL', 'type' => 'url', 'default' => ''],
                'instagram_url' => ['label' => 'Instagram URL', 'type' => 'url', 'default' => ''],
                'linkedin_url' => ['label' => 'LinkedIn URL', 'type' => 'url', 'default' => ''],
                'x_url' => ['label' => 'X/Twitter URL', 'type' => 'url', 'default' => ''],
                'youtube_url' => ['label' => 'YouTube URL', 'type' => 'url', 'default' => ''],
            ],
        ],
    ];

    /** @return array{label: string, type: string, default: string}|null */
    public static function field(string $group, string $key): ?array
    {
        return self::GROUPS[$group]['fields'][$key] ?? null;
    }
}
