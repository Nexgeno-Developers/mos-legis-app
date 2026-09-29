<?php

namespace App\Models;

use App\Enums\SettingGroup;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * SOW A.21 key/value settings. Read through App\Support\Settings (cached), not directly.
 */
#[Fillable(['setting_group', 'setting_key', 'setting_value'])]
class Setting extends Model
{
    protected function casts(): array
    {
        return ['setting_group' => SettingGroup::class];
    }
}
