<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum BlogStatus: string
{
    use HasOptions;

    case Published = 'Published';
    case Draft = 'Draft';
    case Pending = 'Pending';
}
