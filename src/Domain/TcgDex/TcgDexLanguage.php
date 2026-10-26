<?php

declare(strict_types=1);

namespace App\Domain\TcgDex;

use App\Domain\TcgCollector\TcgRegion;

enum TcgDexLanguage: string
{
    case ENGLISH = 'en';
    case JAPANESE = 'ja';

    public static function fromTcgRegion(TcgRegion $region): self
    {
        return match ($region) {
            TcgRegion::INTERNATIONAL => self::ENGLISH,
            TcgRegion::JAPAN => self::JAPANESE,
        };
    }
}
