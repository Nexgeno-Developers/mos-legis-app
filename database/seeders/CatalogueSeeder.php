<?php

namespace Database\Seeders;

use App\Enums\RecordStatus;
use App\Models\AuthorCategory;
use App\Models\BlogCategory;
use App\Models\ContentCategory;
use App\Models\ContentCategoryTheme;
use App\Models\ManuscriptCoAuthorFee;
use App\Models\ManuscriptFee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Starter author/content categories, fee matrix, themes and blog categories
 * taken from the approved wireframes. Idempotent: existing rows are kept.
 */
class CatalogueSeeder extends Seeder
{
    private const AUTHOR_CATEGORIES = [
        'UG Student', 'LLM / PhD Scholar', 'Academician / NGO Worker', 'Advocate',
        'Judge / Retired Judge', 'International Scholar',
    ];

    /** name => [min, max, guideline] */
    private const CONTENT_CATEGORIES = [
        'Research Articles' => [6000, 10000, 'Original doctrinal or empirical scholarship with a clearly stated contribution.'],
        'Legislative Commentaries' => [3000, 6000, 'Clause-by-clause or provision-level analysis of a specific enactment or bill.'],
        'Policy Papers / Policy Briefs' => [2500, 5000, 'Applied policy analysis with concrete recommendations.'],
        'Case Commentaries (Case Notes)' => [2000, 4000, 'Critical commentary on a recent judgment or order.'],
        'Short Articles / Essays' => [1500, 3500, 'Shorter argumentative pieces; footnotes permitted but not required to be exhaustive.'],
        'Notes' => [1200, 3000, 'Narrowly scoped observations on a discrete legal question.'],
        'Book Reviews' => [1000, 2500, 'Evaluative review of a recently published monograph.'],
        'Book Chapters (Call for Chapters)' => [4000, 8000, 'Submitted against an open call for an edited volume; confirm the active call before submitting.'],
    ];

    /** [author category, content category, fee] */
    private const FEES = [
        ['UG Student', 'Research Articles', 1500], ['UG Student', 'Case Commentaries (Case Notes)', 900],
        ['UG Student', 'Notes', 700], ['LLM / PhD Scholar', 'Research Articles', 2500],
        ['LLM / PhD Scholar', 'Legislative Commentaries', 2000], ['LLM / PhD Scholar', 'Policy Papers / Policy Briefs', 1800],
        ['Academician / NGO Worker', 'Research Articles', 3500], ['Academician / NGO Worker', 'Legislative Commentaries', 3000],
        ['Advocate', 'Research Articles', 4000], ['Advocate', 'Case Commentaries (Case Notes)', 2200],
        ['Judge / Retired Judge', 'Research Articles', 0], ['International Scholar', 'Research Articles', 6000],
        ['International Scholar', 'Policy Papers / Policy Briefs', 5000],
    ];

    /**
     * Co-author surcharge grid: [name keywords, fee for each of the 1st/2nd co-authors, fee for each from the 3rd on].
     * Checked in order ("Case Notes" must match case commentaries before notes).
     */
    private const CO_AUTHOR_RATES = [
        [['research'], 300, 200],
        [['legislative', 'policy'], 270, 180],
        [['case'], 225, 150],
        [['book review'], 150, 100],
        [['book chapter'], 420, 280],
        [['short', 'note'], 180, 120],
    ];

    /** @return array{0: int, 1: int}|null Co-author rates for a content category name. */
    public static function coAuthorRatesFor(string $name): ?array
    {
        $name = strtolower($name);

        foreach (self::CO_AUTHOR_RATES as [$keywords, $firstTwo, $additional]) {
            foreach ($keywords as $keyword) {
                if (str_contains($name, $keyword)) {
                    return [$firstTwo, $additional];
                }
            }
        }

        return null;
    }

    private const BLOG_CATEGORIES = ['Constitutional Law', 'Technology & Data', 'Criminal Justice', 'Corporate & Commercial', 'Editorial Board'];

    public function run(): void
    {
        $authors = collect(self::AUTHOR_CATEGORIES)->mapWithKeys(fn (string $name) => [
            $name => AuthorCategory::firstOrCreate(['name' => $name], ['status' => RecordStatus::Active]),
        ]);

        $contents = collect(self::CONTENT_CATEGORIES)->map(fn (array $row, string $name) => ContentCategory::firstOrCreate(
            ['name' => $name],
            ['min_word_limit' => $row[0], 'max_word_limit' => $row[1], 'guideline' => $row[2], 'status' => RecordStatus::Active],
        ));

        foreach (self::FEES as [$author, $content, $fee]) {
            ManuscriptFee::firstOrCreate(
                ['author_category_id' => $authors[$author]->id, 'content_category_id' => $contents[$content]->id],
                ['fees' => $fee],
            );
        }

        foreach ($contents as $name => $content) {
            if ($rates = self::coAuthorRatesFor($name)) {
                ManuscriptCoAuthorFee::firstOrCreate(['content_category_id' => $content->id], ['first_two_fee' => $rates[0], 'additional_fee' => $rates[1]]);
            }
        }

        ContentCategoryTheme::firstOrCreate(
            ['content_category_id' => $contents['Research Articles']->id, 'period' => now()->startOfMonth()->toDateString()],
            ['name' => 'Artificial Intelligence in Law', 'volume' => 5],
        );
        ContentCategoryTheme::firstOrCreate(
            ['content_category_id' => $contents['Legislative Commentaries']->id, 'period' => now()->startOfMonth()->toDateString()],
            ['name' => 'Digital Personal Data Protection — One Year On', 'volume' => 5],
        );

        foreach (self::BLOG_CATEGORIES as $name) {
            BlogCategory::firstOrCreate(['slug' => Str::slug($name)], ['category_name' => $name, 'status' => RecordStatus::Active]);
        }
    }
}
