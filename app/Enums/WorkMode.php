<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum WorkMode: string
{
    use HasOptions;

    case Remote = 'Remote';
    case Hybrid = 'Hybrid';
    case OnSite = 'On-site';
}
