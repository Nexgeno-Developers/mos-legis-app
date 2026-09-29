<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum CommentStatus: string
{
    use HasOptions;

    case Pending = 'Pending';
    case Approved = 'Approved';
    case Rejected = 'Rejected';
}
