<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum RevisionDecision: string
{
    use HasOptions;

    case Approved = 'approved';
    case Revision = 'revision';
    case Rejected = 'rejected';
}
