<?php

declare(strict_types=1);

namespace App\Domain\Collection\Dashboard;

enum MoverMode: string
{
    case PERCENTAGE = 'pct';
    case ABSOLUTE = 'abs';

    public function getLabel(): string
    {
        return match ($this) {
            self::PERCENTAGE => '%',
            self::ABSOLUTE => '€',
        };
    }
}
