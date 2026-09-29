<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum RecordStatus: string
{
    use HasOptions;

    case Active = 'Active';
    case Inactive = 'Inactive';
}
