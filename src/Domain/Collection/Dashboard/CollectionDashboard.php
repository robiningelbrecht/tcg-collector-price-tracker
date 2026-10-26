<?php

declare(strict_types=1);

namespace App\Domain\Collection\Dashboard;

use App\Domain\Collection\Overview\OverviewCards;
use App\Domain\TcgDex\TcgDexLanguage;

final readonly class CollectionDashboard
{
    private const int MOVER_COUNT = 7;
    private const int INSIGHT_COUNT = 5;
    private const int SET_COUNT = 14;
    private const int CONCENTRATION_CARD_COUNT = 10;

    /**
     * @param list<string>                           $dates
     * @param list<array{id: string, label: string}> $series
     */
    private function __construct(
        private PriceRange $range,
        private MoverMode $moverMode,
        private array $dates,
        private OverviewCards $overviewCards,
        private DashboardCards $cards,
        private array $series,
    ) {
    }

    public static function build(OverviewCards $overviewCards, PriceRange $range, MoverMode $moverMode): self
    {
        $dates = $overviewCards->getPriceDates();
        $ranks = $overviewCards->getRanks();

        $cards = DashboardCards::empty();
        foreach ($overviewCards as $overviewCard) {
            $rank = $ranks[$overviewCard->getId()] ?? null;
            if (null === $rank) {
                continue;
            }
            $card = DashboardCard::create($overviewCard, $rank, $dates, $range);
            if ($card instanceof \App\Domain\Collection\Dashboard\DashboardCard) {
                $cards->add($card);
            }
        }

        return new self(
            range: $range,
            moverMode: $moverMode,
            dates: $dates,
            overviewCards: $overviewCards,
            cards: $cards->sortedBy(fn (DashboardCard $a, DashboardCard $b): int => $a->getRank() <=> $b->getRank()),
            series: self::buildSeries($overviewCards),
        );
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    private static function buildSeries(OverviewCards $cards): array
    {
        $series = [];
        foreach ($cards as $card) {
            $serieId = $card->getSerieId() ?? 'unknown';
            $releaseDate = $card->getSetReleaseDate() ?? '9999-12-31';
            $series[$serieId] ??= ['id' => $serieId, 'label' => $card->getSerieName() ?? $serieId, 'release' => $releaseDate];
            if (TcgDexLanguage::ENGLISH === $card->getLanguage() && null !== $card->getSerieName()) {
                $series[$serieId]['label'] = $card->getSerieName();
            }
            $series[$serieId]['release'] = min($series[$serieId]['release'], $releaseDate);
        }

        usort($series, fn (array $a, array $b): int => $a['release'] <=> $b['release']);

        return array_map(
            fn (array $serie): array => ['id' => $serie['id'], 'label' => $serie['label']],
            $series,
        );
    }

    public function getRange(): PriceRange
    {
        return $this->range;
    }

    public function getMoverMode(): MoverMode
    {
        return $this->moverMode;
    }

    public function getLastDate(): ?\DateTimeImmutable
    {
        return [] === $this->dates ? null : new \DateTimeImmutable(array_last($this->dates));
    }

    public function getCardCount(): int
    {
        return count($this->overviewCards);
    }

    public function getCards(): DashboardCards
    {
        return $this->cards;
    }

    public function getTotalValue(): float
    {
        return $this->cards->getTotalValue();
    }

    public function getChange(): float
    {
        return $this->getTotalValue() - $this->cards->getBaseValue();
    }

    public function getChangePercentage(): float
    {
        return $this->getTotalValue() / $this->cards->getBaseValue() - 1;
    }

    public function getPricedCopyCount(): int
    {
        return (int) $this->cards->sum(fn (DashboardCard $card): int => $card->getCard()->getPricedCount());
    }

    public function getRisingCount(): int
    {
        return count($this->cards->filter(fn (DashboardCard $card): bool => $card->getStats()->getChangePercentage() > 0.001));
    }

    public function getFallingCount(): int
    {
        return count($this->cards->filter(fn (DashboardCard $card): bool => $card->getStats()->getChangePercentage() < -0.001));
    }

    public function getFlatCount(): int
    {
        return count($this->cards) - $this->getRisingCount() - $this->getFallingCount();
    }

    public function getBestPerformer(): ?DashboardCard
    {
        return $this->sortedByChange(MoverMode::PERCENTAGE)->getFirst();
    }

    public function getWorstPerformer(): ?DashboardCard
    {
        return $this->sortedByChange(MoverMode::PERCENTAGE)->getLast();
    }

    public function getConcentration(): float
    {
        $topValue = $this->cards->slice(0, self::CONCENTRATION_CARD_COUNT)->getTotalValue();

        return $topValue / $this->getTotalValue();
    }

    public function getRisers(): DashboardCards
    {
        return $this->sortedByChange($this->moverMode)
            ->filter(fn (DashboardCard $card): bool => $card->getChange($this->moverMode) > 0)
            ->slice(0, self::MOVER_COUNT);
    }

    public function getFallers(): DashboardCards
    {
        return $this->sortedByChange($this->moverMode)
            ->reverse()
            ->filter(fn (DashboardCard $card): bool => $card->getChange($this->moverMode) < 0)
            ->slice(0, self::MOVER_COUNT);
    }

    public function getMostVolatile(): DashboardCards
    {
        return $this->cards
            ->sortedBy(fn (DashboardCard $a, DashboardCard $b): int => $b->getStats()->getVolatility() <=> $a->getStats()->getVolatility())
            ->slice(0, self::INSIGHT_COUNT);
    }

    public function getNearTwoYearHigh(): DashboardCards
    {
        return $this->cards
            ->sortedBy(fn (DashboardCard $a, DashboardCard $b): int => $b->getTwoYearStats()->getFromHigh() <=> $a->getTwoYearStats()->getFromHigh())
            ->slice(0, self::INSIGHT_COUNT);
    }

    public function getFurthestBelowTwoYearHigh(): DashboardCards
    {
        return $this->cards
            ->sortedBy(fn (DashboardCard $a, DashboardCard $b): int => $a->getTwoYearStats()->getFromHigh() <=> $b->getTwoYearStats()->getFromHigh())
            ->slice(0, self::INSIGHT_COUNT);
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    public function getSeries(): array
    {
        return array_values(array_filter(
            $this->series,
            fn (array $serie): bool => !$this->cards
                ->filter(fn (DashboardCard $card): bool => ($card->getCard()->getSerieId() ?? 'unknown') === $serie['id'])
                ->isEmpty(),
        ));
    }

    /**
     * @return array{labels: list<string>, values: list<int>, days: int}
     */
    public function getValueChartData(): array
    {
        $start = max(0, count($this->dates) - 1 - $this->range->getDays());
        $filledValues = $this->cards->map(
            fn (DashboardCard $card): array => DashboardCard::forwardFill($card->getCard()->getValuesOn($this->dates)),
        );

        $values = [];
        $counter = count($this->dates);
        for ($index = $start; $index < $counter; ++$index) {
            $values[] = (int) round(array_sum(array_map(fn (array $cardValues): float => $cardValues[$index] ?? 0.0, $filledValues)));
        }

        return [
            'labels' => array_slice($this->dates, $start),
            'values' => $values,
            'days' => $this->range->getDays(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function getSeriesChartData(): array
    {
        $series = $this->getSeries();

        return [
            'labels' => array_column($series, 'label'),
            'values' => array_map(
                fn (array $serie): float => $this->cards
                    ->filter(fn (DashboardCard $card): bool => ($card->getCard()->getSerieId() ?? 'unknown') === $serie['id'])
                    ->getTotalValue(),
                $series,
            ),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>, counts: list<int>, changes: list<float>, range: string}
     */
    public function getSetChartData(): array
    {
        $sets = [];
        foreach ($this->cards as $card) {
            $sets[$card->getCard()->getSetName()][] = $card;
        }

        $sets = array_map(DashboardCards::fromArray(...), $sets);
        uasort($sets, fn (DashboardCards $a, DashboardCards $b): int => $b->getTotalValue() <=> $a->getTotalValue());
        $sets = array_slice($sets, 0, self::SET_COUNT, true);

        return [
            'labels' => array_map(strval(...), array_keys($sets)),
            'values' => array_values(array_map(fn (DashboardCards $cards): int => (int) round($cards->getTotalValue()), $sets)),
            'counts' => array_values(array_map(count(...), $sets)),
            'changes' => array_values(array_map(fn (DashboardCards $cards): float => $cards->getTotalValue() / $cards->getBaseValue() - 1, $sets)),
            'range' => $this->range->getLabel(),
        ];
    }

    private function sortedByChange(MoverMode $mode): DashboardCards
    {
        return $this->cards->sortedBy(fn (DashboardCard $a, DashboardCard $b): int => $b->getChange($mode) <=> $a->getChange($mode));
    }
}
