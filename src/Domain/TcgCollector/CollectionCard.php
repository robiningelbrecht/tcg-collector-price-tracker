<?php

declare(strict_types=1);

namespace App\Domain\TcgCollector;

final readonly class CollectionCard
{
    public function __construct(
        private int $tcgCollectorCardId,
        private int $variantTypeId,
        private int $languageId,
        private string $name,
        private string $setName,
        private string $cardNumber,
        private string $variantName,
        private TcgRegion $region,
        private int $count,
    ) {
    }

    public function getTcgCollectorCardId(): int
    {
        return $this->tcgCollectorCardId;
    }

    public function getVariantTypeId(): int
    {
        return $this->variantTypeId;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSetName(): string
    {
        return $this->setName;
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function getVariantName(): string
    {
        return $this->variantName;
    }

    public function getRegion(): TcgRegion
    {
        return $this->region;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
