<?php

declare(strict_types=1);

namespace App\Domain\Collection\Dashboard;

enum PriceRange: string
{
    case SEVEN_DAYS = '7d';
    case THIRTY_DAYS = '30d';
    case NINETY_DAYS = '90d';
    case SIX_MONTHS = '6m';
    case ONE_YEAR = '1y';
    case TWO_YEARS = '2y';

    public function getLabel(): string
    {
        return strtoupper($this->value);
    }

    public function getDays(): int
    {
        return match ($this) {
            self::SEVEN_DAYS => 7,
            self::THIRTY_DAYS => 30,
            self::NINETY_DAYS => 90,
            self::SIX_MONTHS => 182,
            self::ONE_YEAR => 365,
            self::TWO_YEARS => 729,
        };
    }

    public function isCoveredBy(int $historyDays): bool
    {
        return $historyDays >= $this->getDays();
    }

    public static function longestCoveredBy(int $historyDays): self
    {
        $covered = array_filter(self::cases(), fn (self $range): bool => $range->isCoveredBy($historyDays));

        return [] === $covered ? self::SEVEN_DAYS : end($covered);
    }

    public function orLongestCoveredBy(int $historyDays): self
    {
        return $this->isCoveredBy($historyDays) ? $this : self::longestCoveredBy($historyDays);
    }
}
