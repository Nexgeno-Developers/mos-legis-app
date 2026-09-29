<?php

namespace App\Support;

use App\Enums\MetaType;
use App\Enums\PageTemplate;

/**
 * Template-specific fields stored in page_metas (SOW A.11). Repeatable
 * groups are stored as one JSON meta per key.
 */
final class PageTemplates
{
    /**
     * @return array<string, array{label: string, type: string, columns?: array<string, array{label: string, type: string}>}>
     */
    public static function fields(PageTemplate $template): array
    {
        return match ($template) {
            PageTemplate::Teams => [
                'members' => ['label' => 'Team members', 'type' => 'repeater', 'columns' => [
                    'name' => ['label' => 'Name', 'type' => 'text'],
                    'designation' => ['label' => 'Designation', 'type' => 'text'],
                    'group' => ['label' => 'Group (Founder, Board, Advisory…)', 'type' => 'text'],
                    'overview' => ['label' => 'Overview', 'type' => 'textarea'],
                ]],
            ],
            PageTemplate::Patron => [
                'entries' => ['label' => 'Patron acknowledgements', 'type' => 'repeater', 'columns' => [
                    'description' => ['label' => 'Description', 'type' => 'textarea'],
                    'month_year' => ['label' => 'Date (Month/Year)', 'type' => 'text'],
                ]],
            ],
            PageTemplate::PaperWinner => [
                'winner_choose_desc' => ['label' => 'How the winner is chosen', 'type' => 'textarea'],
                'prize_desc' => ['label' => 'Prize description', 'type' => 'textarea'],
                'be_considered_desc' => ['label' => 'Be considered next month', 'type' => 'textarea'],
            ],
            PageTemplate::Contact => [
                'chief_editor_email' => ['label' => 'Chief editor email', 'type' => 'email'],
                'general_query_email' => ['label' => 'General query email', 'type' => 'email'],
                'telephone' => ['label' => 'Telephone', 'type' => 'text'],
                'office_address' => ['label' => 'Office address', 'type' => 'textarea'],
                'desk_hours' => ['label' => 'Desk hours', 'type' => 'text'],
                'faqs' => ['label' => 'FAQs', 'type' => 'repeater', 'columns' => [
                    'question' => ['label' => 'Question', 'type' => 'text'],
                    'answer' => ['label' => 'Answer', 'type' => 'textarea'],
                ]],
            ],
            PageTemplate::Career => [
                'apply_email' => ['label' => 'Careers email (shown on the page)', 'type' => 'email'],
            ],
            PageTemplate::Layout => [],
        };
    }

    public static function metaType(array $field): MetaType
    {
        return match ($field['type']) {
            'repeater' => MetaType::Json,
            'textarea' => MetaType::Text,
            default => MetaType::String,
        };
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            PageTemplate::Layout->value => 'Default layout',
            PageTemplate::Teams->value => 'Teams',
            PageTemplate::Patron->value => 'Patron',
            PageTemplate::PaperWinner->value => 'Paper winner',
            PageTemplate::Contact->value => 'Contact',
            PageTemplate::Career->value => 'Careers',
        ];
    }
}
