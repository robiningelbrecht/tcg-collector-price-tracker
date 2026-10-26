<?php

declare(strict_types=1);

namespace App\Domain\Collection\Dashboard;

use App\Domain\Collection\Overview\OverviewCard;
use App\Domain\Collection\Overview\OverviewCards;

final readonly class CardDetail
{
    private const int MOVING_AVERAGE_DAYS = 30;
    private const array CHANGE_RANGES = [
        PriceRange::SEVEN_DAYS,
        PriceRange::THIRTY_DAYS,
        PriceRange::NINETY_DAYS,
        PriceRange::ONE_YEAR,
        PriceRange::TWO_YEARS,
    ];

    /**
     * @param list<string>     $dates
     * @param list<float|null> $values
     */
    private function __construct(
        private OverviewCard $card,
        private ?int $rank,
        private PriceRange $range,
        private array $dates,
        private array $values,
    ) {
    }

    public static function build(OverviewCards $cards, OverviewCard $card, PriceRange $range): self
    {
        $dates = $cards->getPriceDates();

        return new self(
            card: $card,
            rank: $cards->getRanks()[$card->getId()] ?? null,
            range: $range,
            dates: $dates,
            values: DashboardCard::forwardFill($card->getValuesOn($dates)),
        );
    }

    public function getCard(): OverviewCard
    {
        return $this->card;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function getRange(): PriceRange
    {
        return $this->range;
    }

    public function getStats(): ?PriceStats
    {
        return PriceStats::calculate($this->dates, $this->values, $this->range);
    }

    /**
     * @return list<array{range: PriceRange, changePercentage: float}>
     */
    public function getChanges(): array
    {
        $changes = [];
        foreach (self::CHANGE_RANGES as $range) {
            $stats = PriceStats::calculate($this->dates, $this->values, $range);
            if ($stats instanceof \App\Domain\Collection\Dashboard\PriceStats) {
                $changes[] = ['range' => $range, 'changePercentage' => $stats->getChangePercentage()];
            }
        }

        return $changes;
    }

    /**
     * @return array{labels: list<string>, values: list<float|null>, average: list<float|null>, days: int, trend: string}
     */
    public function getChartData(): array
    {
        $start = max(0, count($this->values) - 1 - $this->range->getDays());
        $stats = $this->getStats();

        return [
            'labels' => array_slice($this->dates, $start),
            'values' => array_slice($this->values, $start),
            'average' => array_slice(PriceStats::movingAverage($this->values, self::MOVING_AVERAGE_DAYS), $start),
            'days' => $this->range->getDays(),
            'trend' => $stats instanceof \App\Domain\Collection\Dashboard\PriceStats && $stats->getChange() < 0 ? 'down' : 'up',
        ];
    }
}
