<?php

declare(strict_types=1);

namespace App\Domain\Sealed\Overview;

use App\Domain\Collection\Dashboard\PriceStats;

final readonly class SealedOverviewProduct
{
    private const int MOVING_AVERAGE_DAYS = 30;
    private const int SPARKLINE_DAYS = 90;

    /**
     * @param array<string, float> $trendHistory
     */
    private function __construct(
        private int $productId,
        private string $name,
        private ?string $categoryName,
        private int $quantity,
        private ?float $paidPrice,
        private ?float $trend,
        private ?float $low,
        private ?\DateTimeImmutable $priceDate,
        private array $trendHistory,
    ) {
    }

    /**
     * @param array<string, float> $trendHistory
     */
    public static function create(
        int $productId,
        string $name,
        ?string $categoryName,
        int $quantity,
        ?float $paidPrice,
        ?float $trend,
        ?float $low,
        ?\DateTimeImmutable $priceDate,
        array $trendHistory,
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
            trendHistory: $trendHistory,
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

    public function hasPriceHistory(): bool
    {
        return [] !== $this->trendHistory;
    }

    /**
     * @return array{labels: list<string>, values: list<float>, average: list<float|null>, days: int, trend: string}
     */
    public function getChartData(): array
    {
        $values = $this->getValueHistory();

        return [
            'labels' => array_map(strval(...), array_keys($this->trendHistory)),
            'values' => $values,
            'average' => PriceStats::movingAverage($values, self::MOVING_AVERAGE_DAYS),
            'days' => count($values),
            'trend' => $this->getDirection($values),
        ];
    }

    /**
     * @return list<float>
     */
    public function getSparkline(): array
    {
        return array_slice($this->getValueHistory(), -self::SPARKLINE_DAYS);
    }

    public function getSparklineTrend(): string
    {
        return $this->getDirection($this->getSparkline());
    }

    /**
     * @return list<float>
     */
    private function getValueHistory(): array
    {
        return array_map(fn (float $trend): float => round($trend * $this->quantity, 2), array_values($this->trendHistory));
    }

    /**
     * @param list<float> $values
     */
    private function getDirection(array $values): string
    {
        $first = $values[0] ?? null;
        $last = $values[count($values) - 1] ?? null;

        return match (true) {
            null === $first || null === $last || $first === $last => 'flat',
            $last < $first => 'down',
            default => 'up',
        };
    }
}
