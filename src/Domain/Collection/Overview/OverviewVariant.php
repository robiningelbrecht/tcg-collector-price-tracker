<?php

declare(strict_types=1);

namespace App\Domain\Collection\Overview;

final readonly class OverviewVariant
{
    private function __construct(
        private string $variantName,
        private int $count,
        private ?string $priceVariantId,
        private PricePoints $prices,
    ) {
    }

    public static function fromState(
        string $variantName,
        int $count,
        ?string $priceVariantId,
        PricePoints $prices,
    ): self {
        return new self(
            variantName: $variantName,
            count: $count,
            priceVariantId: $priceVariantId,
            prices: $prices,
        );
    }

    public function getVariantName(): string
    {
        return $this->variantName;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getPriceVariantId(): ?string
    {
        return $this->priceVariantId;
    }

    public function getPrices(): PricePoints
    {
        return $this->prices;
    }

    public function isPriced(): bool
    {
        return !$this->prices->isEmpty();
    }

    public function getLatestPrice(): ?PricePoint
    {
        return $this->prices->getLast();
    }
}
