<?php

declare(strict_types=1);

namespace App\Domain\Sealed\Overview;

use App\Infrastructure\ValueObject\Collection;

/**
 * @extends Collection<SealedOverviewProduct>
 */
final class SealedOverviewProducts extends Collection
{
    public function getItemClassName(): string
    {
        return SealedOverviewProduct::class;
    }

    public function getQuantity(): int
    {
        return (int) $this->sum(fn (SealedOverviewProduct $product): int => $product->getQuantity());
    }

    public function getValue(): float
    {
        return (float) $this->sum(fn (SealedOverviewProduct $product): ?float => $product->getValue());
    }

    public function getTotalPaid(): ?float
    {
        $paidProducts = $this->filter(fn (SealedOverviewProduct $product): bool => null !== $product->getPaidPrice());

        return $paidProducts->isEmpty() ? null : (float) $paidProducts->sum(fn (SealedOverviewProduct $product): ?float => $product->getTotalPaid());
    }

    public function getProfit(): ?float
    {
        $profitableProducts = $this->filter(fn (SealedOverviewProduct $product): bool => null !== $product->getProfit());

        return $profitableProducts->isEmpty() ? null : (float) $profitableProducts->sum(fn (SealedOverviewProduct $product): ?float => $product->getProfit());
    }

    public function getUnpricedCount(): int
    {
        return count($this->filter(fn (SealedOverviewProduct $product): bool => null === $product->getTrend()));
    }

    /**
     * @return array<int>
     */
    public function getProductIds(): array
    {
        /** @var array<int> $productIds */
        $productIds = $this->map(fn (SealedOverviewProduct $product): int => $product->getProductId());

        return $productIds;
    }
}
