<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PlagiarismCheckType: string
{
    use HasOptions;

    case Manuscript = 'manuscript';
    case Standalone = 'standalone';
}
