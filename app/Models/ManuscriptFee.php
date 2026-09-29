<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SOW A.15 — publication fee for one Author × Content category combination.
 */
#[Fillable(['content_category_id', 'author_category_id', 'fees'])]
class ManuscriptFee extends Model
{
    protected function casts(): array
    {
        return ['fees' => 'decimal:2'];
    }

    public function contentCategory(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class, 'content_category_id');
    }

    public function authorCategory(): BelongsTo
    {
        return $this->belongsTo(AuthorCategory::class, 'author_category_id');
    }
}
