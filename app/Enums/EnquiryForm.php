<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum EnquiryForm: string
{
    use HasOptions;

    case Contact = 'contact';
    case Career = 'career';
}
