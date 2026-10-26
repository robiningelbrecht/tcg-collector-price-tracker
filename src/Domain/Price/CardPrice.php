<?php

declare(strict_types=1);

namespace App\Domain\Price;

use App\Domain\TcgDex\TcgDexLanguage;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'CardPrice')]
#[ORM\Index(name: 'CardPrice_date', columns: ['date'])]
final readonly class CardPrice
{
    /**
     * @param array<string, mixed> $data
     */
    private function __construct(
        #[ORM\Id, ORM\Column(type: 'string', enumType: TcgDexLanguage::class)]
        private TcgDexLanguage $language,
        #[ORM\Id, ORM\Column(type: 'string')]
        private string $cardId,
        #[ORM\Id, ORM\Column(type: 'string')]
        private string $variantId,
        #[ORM\Id, ORM\Column(type: 'string', enumType: PriceSource::class)]
        private PriceSource $source,
        #[ORM\Id, ORM\Column(type: 'date_immutable')]
        private \DateTimeImmutable $date,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $variantType,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $variantSubtype,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $variantSize,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $variantStamps,
        #[ORM\Column(type: 'string')]
        private string $currency,
        #[ORM\Column(type: 'float', nullable: true)]
        private ?float $low,
        #[ORM\Column(type: 'float', nullable: true)]
        private ?float $mid,
        #[ORM\Column(type: 'float', nullable: true)]
        private ?float $high,
        #[ORM\Column(type: 'float', nullable: true)]
        private ?float $market,
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
     * @param array<string, mixed> $data
     */
    public static function create(
        TcgDexLanguage $language,
        string $cardId,
        string $variantId,
        PriceSource $source,
        \DateTimeImmutable $date,
        ?string $variantType,
        ?string $variantSubtype,
        ?string $variantSize,
        ?string $variantStamps,
        string $currency,
        ?float $low,
        ?float $mid,
        ?float $high,
        ?float $market,
        ?float $avg,
        ?float $trend,
        ?float $avg1,
        ?float $avg7,
        ?float $avg30,
        ?\DateTimeImmutable $sourceUpdatedOn,
        array $data,
    ): self {
        return new self(
            language: $language,
            cardId: $cardId,
            variantId: $variantId,
            source: $source,
            date: $date,
            variantType: $variantType,
            variantSubtype: $variantSubtype,
            variantSize: $variantSize,
            variantStamps: $variantStamps,
            currency: $currency,
            low: $low,
            mid: $mid,
            high: $high,
            market: $market,
            avg: $avg,
            trend: $trend,
            avg1: $avg1,
            avg7: $avg7,
            avg30: $avg30,
            sourceUpdatedOn: $sourceUpdatedOn,
            data: $data,
        );
    }

    public function getLanguage(): TcgDexLanguage
    {
        return $this->language;
    }

    public function getCardId(): string
    {
        return $this->cardId;
    }

    public function getVariantId(): string
    {
        return $this->variantId;
    }

    public function getSource(): PriceSource
    {
        return $this->source;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getVariantType(): ?string
    {
        return $this->variantType;
    }

    public function getVariantSubtype(): ?string
    {
        return $this->variantSubtype;
    }

    public function getVariantSize(): ?string
    {
        return $this->variantSize;
    }

    public function getVariantStamps(): ?string
    {
        return $this->variantStamps;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getLow(): ?float
    {
        return $this->low;
    }

    public function getMid(): ?float
    {
        return $this->mid;
    }

    public function getHigh(): ?float
    {
        return $this->high;
    }

    public function getMarket(): ?float
    {
        return $this->market;
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
