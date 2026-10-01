<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/** Where a menu is rendered on the public website. */
enum MenuLocation: string
{
    use HasOptions;

    case Header = 'header';
    case Footer = 'footer';

    public function label(): string
    {
        return match ($this) {
            self::Header => 'Header menu',
            self::Footer => 'Footer menu',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Header => 'Main navigation bar. A group becomes a dropdown; its links appear inside it.',
            self::Footer => 'Footer link columns. Each group becomes a column with its name as the heading.',
        };
    }
}
