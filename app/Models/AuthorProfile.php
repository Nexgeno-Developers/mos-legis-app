<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Author-specific profile data, kept apart from authentication (clarification #4).
 */
#[Fillable(['user_id', 'author_category_id', 'institution', 'country', 'bio', 'orcid', 'profile_picture'])]
class AuthorProfile extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authorCategory(): BelongsTo
    {
        return $this->belongsTo(AuthorCategory::class, 'author_category_id');
    }
}
