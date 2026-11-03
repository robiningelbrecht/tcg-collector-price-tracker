<?php

declare(strict_types=1);

namespace App\Domain\Sealed\Overview;

final readonly class SealedOverviewProduct
{
    private function __construct(
        private int $productId,
        private string $name,
        private ?string $categoryName,
        private int $quantity,
        private ?float $paidPrice,
        private ?float $trend,
        private ?float $low,
        private ?\DateTimeImmutable $priceDate,
    ) {
    }

    public static function create(
        int $productId,
        string $name,
        ?string $categoryName,
        int $quantity,
        ?float $paidPrice,
        ?float $trend,
        ?float $low,
        ?\DateTimeImmutable $priceDate,
    ): self {
        return new self(
            productId: $productId,
            name: $name,
            categoryName: $categoryName,
            quantity: $quantity,
            paidPrice: $paidPrice,
            trend: $trend,
            low: $low,
            priceDate: $priceDate,
        );
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getPaidPrice(): ?float
    {
        return $this->paidPrice;
    }

    public function getTrend(): ?float
    {
        return $this->trend;
    }

    public function getLow(): ?float
    {
        return $this->low;
    }

    public function getPriceDate(): ?\DateTimeImmutable
    {
        return $this->priceDate;
    }

    public function getValue(): ?float
    {
        return null === $this->trend ? null : $this->trend * $this->quantity;
    }

    public function getTotalPaid(): ?float
    {
        return null === $this->paidPrice ? null : $this->paidPrice * $this->quantity;
    }

    public function getProfit(): ?float
    {
        $value = $this->getValue();
        $totalPaid = $this->getTotalPaid();

        return null === $value || null === $totalPaid ? null : $value - $totalPaid;
    }
}
