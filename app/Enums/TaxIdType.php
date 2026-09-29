<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum TaxIdType: string
{
    use HasOptions;

    case Gst = 'gst';
    case Vat = 'vat';
    case None = 'none';
}
