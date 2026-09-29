<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum AwardPeriodType: string
{
    use HasOptions;

    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
}
