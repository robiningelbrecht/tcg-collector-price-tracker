<?php

declare(strict_types=1);

namespace App\Domain\Collection\Dashboard;

final readonly class PriceStats
{
    private function __construct(
        private bool $complete,
        private \DateTimeImmutable $firstOn,
        private float $first,
        private float $last,
        private float $high,
        private \DateTimeImmutable $highOn,
        private float $low,
        private \DateTimeImmutable $lowOn,
        private float $maxDrawdown,
        private float $volatility,
    ) {
    }

    /**
     * @param list<string>     $dates
     * @param list<float|null> $values
     */
    public static function calculate(array $dates, array $values, PriceRange $range): ?self
    {
        $start = max(0, count($values) - 1 - $range->getDays());
        $window = array_filter(
            array_slice($values, $start, preserve_keys: true),
            fn (?float $value): bool => null !== $value,
        );
        if ([] === $window) {
            return null;
        }

        $first = reset($window);
        $last = end($window);
        $high = -INF;
        $low = INF;
        $highIndex = $lowIndex = array_key_first($window);
        $peak = -INF;
        $maxDrawdown = 0.0;
        $logReturns = [];
        $previous = null;

        foreach ($window as $index => $value) {
            if ($value > $high) {
                $high = $value;
                $highIndex = $index;
            }
            if ($value < $low) {
                $low = $value;
                $lowIndex = $index;
            }
            $peak = max($peak, $value);
            $maxDrawdown = min($maxDrawdown, $value / $peak - 1);
            if (null !== $previous) {
                $logReturns[] = log($value / $previous);
            }
            $previous = $value;
        }

        return new self(
            complete: $range->isCoveredBy(count($values) - 1) && null !== $values[$start],
            firstOn: new \DateTimeImmutable($dates[array_key_first($window)]),
            first: $first,
            last: $last,
            high: $high,
            highOn: new \DateTimeImmutable($dates[$highIndex]),
            low: $low,
            lowOn: new \DateTimeImmutable($dates[$lowIndex]),
            maxDrawdown: $maxDrawdown,
            volatility: self::standardDeviation($logReturns) * sqrt(365),
        );
    }

    /**
     * @param list<float|null> $values
     *
     * @return list<float|null>
     */
    public static function movingAverage(array $values, int $window): array
    {
        $averages = [];
        foreach ($values as $index => $value) {
            $known = array_filter(
                array_slice($values, max(0, $index - $window + 1), min($window, $index + 1)),
                fn (?float $windowValue): bool => null !== $windowValue,
            );
            $averages[] = null === $value || count($known) < min($window, 5)
                ? null
                : round(array_sum($known) / count($known), 2);
        }

        return $averages;
    }

    /**
     * @param list<float> $values
     */
    private static function standardDeviation(array $values): float
    {
        $count = count($values);
        if ($count < 2) {
            return 0.0;
        }

        $mean = array_sum($values) / $count;
        $squaredDifferences = array_map(fn (float $value): float => ($value - $mean) ** 2, $values);

        return sqrt(array_sum($squaredDifferences) / ($count - 1));
    }

    public function isComplete(): bool
    {
        return $this->complete;
    }

    public function getFirstOn(): \DateTimeImmutable
    {
        return $this->firstOn;
    }

    public function getFirst(): float
    {
        return $this->first;
    }

    public function getLast(): float
    {
        return $this->last;
    }

    public function getChange(): float
    {
        return $this->last - $this->first;
    }

    public function getChangePercentage(): float
    {
        return $this->last / $this->first - 1;
    }

    public function getHigh(): float
    {
        return $this->high;
    }

    public function getHighOn(): \DateTimeImmutable
    {
        return $this->highOn;
    }

    public function getLow(): float
    {
        return $this->low;
    }

    public function getLowOn(): \DateTimeImmutable
    {
        return $this->lowOn;
    }

    public function getMaxDrawdown(): float
    {
        return $this->maxDrawdown;
    }

    public function getVolatility(): float
    {
        return $this->volatility;
    }

    public function getFromHigh(): float
    {
        return $this->last / $this->high - 1;
    }

    public function isAtHigh(): bool
    {
        return $this->getFromHigh() > -0.001;
    }
}
