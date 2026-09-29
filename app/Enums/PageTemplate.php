<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PageTemplate: string
{
    use HasOptions;

    case Layout = 'layout';
    case Teams = 'teams';
    case Patron = 'patron';
    case PaperWinner = 'paper_winner';
    case Contact = 'contact';
    case Career = 'career';
}
