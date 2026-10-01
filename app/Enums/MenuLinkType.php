<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum MenuLinkType: string
{
    use HasOptions;

    case Route = 'route';
    case Page = 'page';
    case Url = 'url';
    case None = 'none';

    public function label(): string
    {
        return match ($this) {
            self::Route => 'Website page',
            self::Page => 'CMS page',
            self::Url => 'Custom URL',
            self::None => 'Group (dropdown / column heading)',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::None => 'Group',
            default => $this->label(),
        };
    }
}
