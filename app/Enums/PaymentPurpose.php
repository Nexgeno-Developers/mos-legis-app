<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PaymentPurpose: string
{
    use HasOptions;

    case Prescreening = 'prescreening';
    case Publication = 'publication';
    case PlagiarismCheck = 'plagiarism_check';

    public function label(): string
    {
        return match ($this) {
            self::Prescreening => 'Plagiarism pre-screening',
            self::Publication => 'Publication',
            self::PlagiarismCheck => 'Standalone plagiarism check',
        };
    }
}
