<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum ApplicationMethod: string
{
    use HasOptions;

    case Email = 'Email';
    case ExternalUrl = 'External URL';
}
