<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PublishStatus: string
{
    use HasOptions;

    case Published = 'Published';
    case Draft = 'Draft';
}
