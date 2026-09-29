<?php

namespace App\Models;

use App\Enums\RecordStatus;
use App\Models\Concerns\HasRecordStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['tag_name', 'slug', 'status', 'user_id'])]
class BlogTag extends Model
{
    use HasFactory, HasRecordStatus;

    protected function casts(): array
    {
        return ['status' => RecordStatus::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_tag_pivot', 'blog_tag_id', 'blog_id');
    }
}
