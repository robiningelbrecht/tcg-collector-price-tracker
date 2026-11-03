<?php

declare(strict_types=1);

namespace App\Domain\Sealed\Price;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'SealedProductPrice')]
#[ORM\Index(name: 'SealedProductPrice_date', columns: ['date'])]
final readonly class SealedProductPrice
{
    /**
     * @param array<string, mixed> $data
     */
    private function __construct(
        #[ORM\Id, ORM\Column(type: 'integer')]
        private int $productId,
        #[ORM\Id, ORM\Column(type: 'date_immutable')]
        private \DateTimeImmutable $date,
        #[ORM\Column(type: 'string')]
        private string $currency,
        #[ORM\Column(type: 'float', nullable: true)]
        private ?float $low,
        #[ORM\Column(type: 'float', nullable: true)]
        private ?float $avg,
        #[ORM\Column(type: 'float', nullable: true)]
        private ?float $trend,
        #[ORM\Column(type: 'float', nullable: true)]
        private ?float $avg1,
        #[ORM\Column(type: 'float', nullable: true)]
        private ?float $avg7,
        #[ORM\Column(type: 'float', nullable: true)]
        private ?float $avg30,
        #[ORM\Column(type: 'datetime_immutable', nullable: true)]
        private ?\DateTimeImmutable $sourceUpdatedOn,
        #[ORM\Column(type: 'json')]
        private array $data,
    ) {
    }

    /**
     * @param array<string, mixed> $prices
     */
    public static function fromCardmarket(
        int $productId,
        \DateTimeImmutable $date,
        array $prices,
        ?\DateTimeImmutable $sourceUpdatedOn,
    ): ?self {
        $values = [];
        foreach (['low', 'avg', 'trend', 'avg1', 'avg7', 'avg30'] as $field) {
            $value = $prices[$field] ?? null;
            $values[$field] = is_int($value) || is_float($value) ? (float) $value : null;
        }

        if ([] === array_filter($values, fn (?float $value): bool => null !== $value)) {
            return null;
        }

        return new self(
            productId: $productId,
            date: $date,
            currency: 'EUR',
            low: $values['low'],
            avg: $values['avg'],
            trend: $values['trend'],
            avg1: $values['avg1'],
            avg7: $values['avg7'],
            avg30: $values['avg30'],
            sourceUpdatedOn: $sourceUpdatedOn,
            data: $prices,
        );
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getLow(): ?float
    {
        return $this->low;
    }

    public function getAvg(): ?float
    {
        return $this->avg;
    }

    public function getTrend(): ?float
    {
        return $this->trend;
    }

    public function getAvg1(): ?float
    {
        return $this->avg1;
    }

    public function getAvg7(): ?float
    {
        return $this->avg7;
    }

    public function getAvg30(): ?float
    {
        return $this->avg30;
    }

    public function getSourceUpdatedOn(): ?\DateTimeImmutable
    {
        return $this->sourceUpdatedOn;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
