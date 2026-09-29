<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum EmploymentType: string
{
    use HasOptions;

    case FullTime = 'Full time';
    case PartTime = 'Part time';
    case Internship = 'Internship';
    case Contract = 'Contract';
    case Fellowship = 'Fellowship';
}
