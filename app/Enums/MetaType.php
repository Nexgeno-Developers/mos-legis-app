<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum MetaType: string
{
    use HasOptions;

    case String = 'string';
    case Text = 'text';
    case Json = 'json';
    case Boolean = 'boolean';
    case Number = 'number';
}
