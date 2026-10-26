<?php

declare(strict_types=1);

namespace App\Domain\Collection\Overview;

final readonly class OverviewVariant
{
    private function __construct(
        private string $label,
        private int $count,
        private ?string $priceVariantId,
        private PricePoints $prices,
        private ?MissingPriceReason $missingPriceReason,
    ) {
    }

    public static function fromState(
        string $label,
        int $count,
        ?string $priceVariantId,
        PricePoints $prices,
        ?MissingPriceReason $missingPriceReason,
    ): self {
        return new self(
            label: $label,
            count: $count,
            priceVariantId: $priceVariantId,
            prices: $prices,
            missingPriceReason: $missingPriceReason,
        );
    }

    public function getLabel(): string
    {
        return $this->label;
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

    public function getMissingPriceReason(): ?MissingPriceReason
    {
        return $this->missingPriceReason;
    }

    public function getLatestPrice(): ?PricePoint
    {
        return $this->prices->getLast();
    }
}
