<?php

declare(strict_types=1);

namespace App\Domain\Collection\Overview;

final readonly class PricePoint
{
    private function __construct(
        private \DateTimeImmutable $date,
        private float $trend,
        private ?float $avg7,
        private ?float $avg30,
    ) {
    }

    public static function fromState(
        \DateTimeImmutable $date,
        float $trend,
        ?float $avg7,
        ?float $avg30,
    ): self {
        return new self(
            date: $date,
            trend: $trend,
            avg7: $avg7,
            avg30: $avg30,
        );
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getTrend(): float
    {
        return $this->trend;
    }

    public function getAvg7(): ?float
    {
        return $this->avg7;
    }

    public function getAvg30(): ?float
    {
        return $this->avg30;
    }
}
