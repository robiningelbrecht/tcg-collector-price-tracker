<?php

declare(strict_types=1);

namespace App\Domain\Collection\Dashboard;

use App\Domain\Collection\Overview\OverviewCard;

final readonly class DashboardCard
{
    /**
     * @param list<float|null> $sparkline
     */
    private function __construct(
        private OverviewCard $card,
        private int $rank,
        private PriceStats $stats,
        private PriceStats $sevenDayStats,
        private PriceStats $twoYearStats,
        private array $sparkline,
    ) {
    }

    /**
     * @param list<string> $dates
     */
    public static function create(OverviewCard $card, int $rank, array $dates, PriceRange $range): ?self
    {
        $values = self::forwardFill($card->getValuesOn($dates));
        $stats = PriceStats::calculate($dates, $values, $range);
        $sevenDayStats = PriceStats::calculate($dates, $values, PriceRange::SEVEN_DAYS);
        $twoYearStats = PriceStats::calculate($dates, $values, PriceRange::TWO_YEARS);
        if (in_array(null, [$stats, $sevenDayStats, $twoYearStats], true)) {
            return null;
        }

        return new self(
            card: $card,
            rank: $rank,
            stats: $stats,
            sevenDayStats: $sevenDayStats,
            twoYearStats: $twoYearStats,
            sparkline: array_slice($values, max(0, count($values) - 1 - $range->getDays())),
        );
    }

    /**
     * @param list<float|null> $values
     *
     * @return list<float|null>
     */
    public static function forwardFill(array $values): array
    {
        $last = null;

        return array_map(function (?float $value) use (&$last): ?float {
            $last = $value ?? $last;

            return $last;
        }, $values);
    }

    public function getCard(): OverviewCard
    {
        return $this->card;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function getValue(): float
    {
        return $this->stats->getLast();
    }

    public function getStats(): PriceStats
    {
        return $this->stats;
    }

    public function getSevenDayStats(): PriceStats
    {
        return $this->sevenDayStats;
    }

    public function getTwoYearStats(): PriceStats
    {
        return $this->twoYearStats;
    }

    /**
     * @return list<float|null>
     */
    public function getSparkline(): array
    {
        return $this->sparkline;
    }

    public function getChange(MoverMode $mode): float
    {
        return match ($mode) {
            MoverMode::PERCENTAGE => $this->stats->getChangePercentage(),
            MoverMode::ABSOLUTE => $this->stats->getChange(),
        };
    }
}
