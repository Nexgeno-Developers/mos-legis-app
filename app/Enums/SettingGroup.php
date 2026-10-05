<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum SettingGroup: string
{
    use HasOptions;

    case General = 'general';
    case Manuscript = 'manuscript';
    case Payment = 'payment';
    case SeoSocial = 'seo_social';
    case Approvals = 'approvals';
}
