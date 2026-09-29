<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * The three roles fixed by SOW A.09/A.10. Superadmin and Reviewer use the
 * admin panel; Author ("Member") uses only the public site and author portal.
 */
enum RoleName: string
{
    use HasOptions;

    case Superadmin = 'superadmin';
    case Reviewer = 'reviewer';
    case Author = 'author';

    public function canAccessAdmin(): bool
    {
        return $this !== self::Author;
    }
}
