<?php

namespace App\Models;

use App\Enums\RecordStatus;
use App\Models\Concerns\HasRecordStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_name', 'slug', 'description', 'status'])]
class BlogCategory extends Model
{
    use HasFactory, HasRecordStatus;

    protected function casts(): array
    {
        return ['status' => RecordStatus::class];
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'category_id');
    }
}
