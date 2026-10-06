<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Co-author surcharge for one content category: `first_two_fee` for each of the 1st and 2nd
 * co-authors, `additional_fee` for each co-author from the 3rd on.
 */
#[Table('manuscript_coauthor_fees')]
#[Fillable(['content_category_id', 'first_two_fee', 'additional_fee'])]
class ManuscriptCoAuthorFee extends Model
{
    protected function casts(): array
    {
        return ['first_two_fee' => 'decimal:2', 'additional_fee' => 'decimal:2'];
    }

    public function contentCategory(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class, 'content_category_id');
    }
}
