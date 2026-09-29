<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PlagiarismCheckStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
