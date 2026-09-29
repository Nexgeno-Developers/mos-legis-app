<?php

namespace App\Models;

use App\Enums\MetaType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['page_id', 'meta_key', 'meta_value', 'meta_type'])]
class PageMeta extends Model
{
    protected function casts(): array
    {
        return ['meta_type' => MetaType::class];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function value(): mixed
    {
        return match ($this->meta_type) {
            MetaType::Json => json_decode((string) $this->meta_value, true) ?? [],
            MetaType::Boolean => (bool) $this->meta_value,
            MetaType::Number => is_numeric($this->meta_value) ? $this->meta_value + 0 : null,
            default => $this->meta_value,
        };
    }
}
