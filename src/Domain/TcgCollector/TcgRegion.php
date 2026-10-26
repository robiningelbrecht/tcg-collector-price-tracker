<?php

declare(strict_types=1);

namespace App\Domain\TcgCollector;

enum TcgRegion: int
{
    case INTERNATIONAL = 1;
    case JAPAN = 2;
}
