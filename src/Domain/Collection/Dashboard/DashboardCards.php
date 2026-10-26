<?php

declare(strict_types=1);

namespace App\Domain\Collection\Dashboard;

use App\Infrastructure\ValueObject\Collection;

/**
 * @extends Collection<DashboardCard>
 */
final class DashboardCards extends Collection
{
    public function getItemClassName(): string
    {
        return DashboardCard::class;
    }

    public function sortedBy(\Closure $compare): self
    {
        return self::fromArray($this->toArray())->usort($compare);
    }

    public function getTotalValue(): float
    {
        return (float) $this->sum(fn (DashboardCard $card): float => $card->getValue());
    }

    public function getBaseValue(): float
    {
        return (float) $this->sum(fn (DashboardCard $card): float => $card->getStats()->getFirst());
    }
}
