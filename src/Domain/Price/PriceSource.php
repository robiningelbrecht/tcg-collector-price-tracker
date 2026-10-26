<?php

declare(strict_types=1);

namespace App\Domain\Price;

enum PriceSource: string
{
    case CARDMARKET = 'cardmarket';
    case TCGPLAYER = 'tcgplayer';
}
