<?php

namespace App\Models;

use App\Enums\RecordStatus;
use App\Models\Concerns\HasRecordStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SOW A.12 — one axis of the fee matrix.
 */
#[Table('manuscript_author_categories')]
#[Fillable(['name', 'status'])]
class AuthorCategory extends Model
{
    use HasFactory, HasRecordStatus;

    protected function casts(): array
    {
        return ['status' => RecordStatus::class];
    }

    public function fees(): HasMany
    {
        return $this->hasMany(ManuscriptFee::class, 'author_category_id');
    }
}
