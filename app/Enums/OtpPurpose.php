<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum OtpPurpose: string
{
    use HasOptions;

    case Registration = 'registration';
    case Login = 'login';
    case PasswordReset = 'password_reset';
}
