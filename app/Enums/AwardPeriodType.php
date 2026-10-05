<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum AwardPeriodType: string
{
    use HasOptions;

    // Best Paper is awarded per quarter only (monthly awards were removed at the client's request).
    case Quarterly = 'quarterly';
}
