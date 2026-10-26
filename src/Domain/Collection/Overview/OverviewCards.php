<?php

declare(strict_types=1);

namespace App\Domain\Collection\Overview;

use App\Domain\TcgDex\TcgDexLanguage;
use App\Infrastructure\ValueObject\Collection;

/**
 * @extends Collection<OverviewCard>
 */
final class OverviewCards extends Collection
{
    public function getItemClassName(): string
    {
        return OverviewCard::class;
    }

    public function findByCardId(TcgDexLanguage $language, string $cardId): ?OverviewCard
    {
        return $this->find(fn (OverviewCard $card): bool => $card->getLanguage() === $language && $card->getCardId() === $cardId);
    }

    /**
     * @return array<string, int>
     */
    public function getRanks(): array
    {
        $priced = $this->filter(fn (OverviewCard $card): bool => null !== $card->getCurrentValue())
            ->usort(fn (OverviewCard $a, OverviewCard $b): int => $b->getCurrentValue() <=> $a->getCurrentValue());

        $ranks = [];
        foreach ($priced as $index => $card) {
            $ranks[$card->getId()] = $index + 1;
        }

        return $ranks;
    }

    public function getHistoryDays(): int
    {
        return max(0, count($this->getPriceDates()) - 1);
    }

    /**
     * @return list<string>
     */
    public function getPriceDates(): array
    {
        $knownDates = [];
        foreach ($this as $card) {
            foreach (array_keys($card->getValueHistory()) as $date) {
                $knownDates[$date] = true;
            }
        }
        if ([] === $knownDates) {
            return [];
        }

        ksort($knownDates);
        $period = new \DatePeriod(
            new \DateTimeImmutable(array_key_first($knownDates)),
            new \DateInterval('P1D'),
            new \DateTimeImmutable(array_key_last($knownDates)),
            \DatePeriod::INCLUDE_END_DATE,
        );

        $dates = [];
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }
}
