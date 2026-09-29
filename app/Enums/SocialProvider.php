<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum SocialProvider: string
{
    use HasOptions;

    case Google = 'google';
    case Orcid = 'orcid';
}
