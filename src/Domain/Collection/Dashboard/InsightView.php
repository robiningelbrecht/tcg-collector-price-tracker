<?php

declare(strict_types=1);

namespace App\Domain\Collection\Dashboard;

enum InsightView: string
{
    case MOST_VOLATILE = 'volatile';
    case NEAR_TWO_YEAR_HIGH = 'near-high';
    case BELOW_TWO_YEAR_HIGH = 'below-high';

    public function getLabel(): string
    {
        return match ($this) {
            self::MOST_VOLATILE => 'Volatile',
            self::NEAR_TWO_YEAR_HIGH => 'Near high',
            self::BELOW_TWO_YEAR_HIGH => 'Below high',
        };
    }

    public function getTitle(): string
    {
        return match ($this) {
            self::MOST_VOLATILE => 'Most volatile',
            self::NEAR_TWO_YEAR_HIGH => 'At or near 2-year high',
            self::BELOW_TWO_YEAR_HIGH => 'Furthest below 2-year high',
        };
    }
}
