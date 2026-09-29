<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PaymentPurpose: string
{
    use HasOptions;

    case Prescreening = 'prescreening';
    case Publication = 'publication';
    case PlagiarismCheck = 'plagiarism_check';
}
