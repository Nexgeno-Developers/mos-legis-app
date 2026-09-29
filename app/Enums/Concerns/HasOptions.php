<?php

namespace App\Enums\Concerns;

use Illuminate\Support\Str;

trait HasOptions
{
    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return array<string, string> value => label, for <select> options */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }

    public function label(): string
    {
        return Str::of($this->value)->replace(['_', '-'], ' ')->ucfirst()->toString();
    }
}
