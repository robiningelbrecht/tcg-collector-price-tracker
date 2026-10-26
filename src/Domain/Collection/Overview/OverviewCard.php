<?php

declare(strict_types=1);

namespace App\Domain\Collection\Overview;

use App\Domain\TcgDex\TcgDexLanguage;

final readonly class OverviewCard
{
    private function __construct(
        private TcgDexLanguage $language,
        private string $cardId,
        private string $localId,
        private string $name,
        private ?string $rarity,
        private ?string $image,
        private string $cardSetId,
        private string $setName,
        private ?int $setCardCountOfficial,
        private ?string $setReleaseDate,
        private ?string $serieId,
        private ?string $serieName,
        private OverviewVariants $variants,
    ) {
    }

    public static function fromState(
        TcgDexLanguage $language,
        string $cardId,
        string $localId,
        string $name,
        ?string $rarity,
        ?string $image,
        string $cardSetId,
        string $setName,
        ?int $setCardCountOfficial,
        ?string $setReleaseDate,
        ?string $serieId,
        ?string $serieName,
        OverviewVariants $variants,
    ): self {
        return new self(
            language: $language,
            cardId: $cardId,
            localId: $localId,
            name: $name,
            rarity: $rarity,
            image: $image,
            cardSetId: $cardSetId,
            setName: $setName,
            setCardCountOfficial: $setCardCountOfficial,
            setReleaseDate: $setReleaseDate,
            serieId: $serieId,
            serieName: $serieName,
            variants: $variants,
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

    public function getId(): string
    {
        return $this->language->value.'/'.$this->cardId;
    }

    public function getLocalId(): string
    {
        return $this->localId;
    }

    public function getNumber(): string
    {
        return null === $this->setCardCountOfficial ? $this->localId : $this->localId.'/'.$this->setCardCountOfficial;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRarity(): ?string
    {
        return $this->rarity;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getCardSetId(): string
    {
        return $this->cardSetId;
    }

    public function getSetName(): string
    {
        return $this->setName;
    }

    public function getSetCardCountOfficial(): ?int
    {
        return $this->setCardCountOfficial;
    }

    public function getSetReleaseDate(): ?string
    {
        return $this->setReleaseDate;
    }

    public function isFromNewSet(): bool
    {
        return null !== $this->setReleaseDate && new \DateTimeImmutable($this->setReleaseDate) > new \DateTimeImmutable('-1 year');
    }

    public function getSerieId(): ?string
    {
        return $this->serieId;
    }

    public function getSerieName(): ?string
    {
        return $this->serieName;
    }

    public function getVariants(): OverviewVariants
    {
        return $this->variants;
    }

    public function getCount(): int
    {
        return (int) $this->variants->sum(fn (OverviewVariant $variant): int => $variant->getCount());
    }

    public function getPricedCount(): int
    {
        return (int) $this->variants
            ->filter(fn (OverviewVariant $variant): bool => $variant->isPriced())
            ->sum(fn (OverviewVariant $variant): int => $variant->getCount());
    }

    public function getUnpricedCount(): int
    {
        return $this->getCount() - $this->getPricedCount();
    }

    public function isPriced(): bool
    {
        return $this->getPricedCount() > 0;
    }

    /**
     * @return array<string, float>
     */
    public function getValueHistory(): array
    {
        $values = [];
        foreach ($this->variants as $variant) {
            foreach ($variant->getPrices() as $price) {
                $date = $price->getDate()->format('Y-m-d');
                $values[$date] = ($values[$date] ?? 0.0) + $price->getTrend() * $variant->getCount();
            }
        }
        ksort($values);

        return $values;
    }

    public function getCurrentValue(): ?float
    {
        $values = $this->getValueHistory();

        return [] === $values ? null : end($values);
    }

    /**
     * @param list<string> $dates
     *
     * @return list<float|null>
     */
    public function getValuesOn(array $dates): array
    {
        $values = $this->getValueHistory();

        return array_map(
            fn (string $date): ?float => isset($values[$date]) ? round($values[$date], 2) : null,
            $dates,
        );
    }
}
