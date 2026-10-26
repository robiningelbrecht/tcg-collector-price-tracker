<?php

declare(strict_types=1);

namespace App\Domain\Collection;

use App\Domain\TcgCollector\TcgRegion;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'OwnedCard')]
#[ORM\Index(name: 'OwnedCard_setName', columns: ['setName'])]
final readonly class OwnedCard
{
    private function __construct(
        #[ORM\Id, ORM\Column(type: 'integer')]
        private int $tcgCollectorCardId,
        #[ORM\Id, ORM\Column(type: 'integer')]
        private int $variantTypeId,
        #[ORM\Id, ORM\Column(type: 'integer')]
        private int $languageId,
        #[ORM\Column(type: 'string')]
        private string $name,
        #[ORM\Column(type: 'string')]
        private string $setName,
        #[ORM\Column(type: 'string')]
        private string $cardNumber,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $rarity,
        #[ORM\Column(type: 'string')]
        private string $variantName,
        #[ORM\Column(type: 'integer', enumType: TcgRegion::class)]
        private TcgRegion $region,
        #[ORM\Column(type: 'integer')]
        private int $count,
        #[ORM\Column(type: 'string')]
        private string $tcgCollectorSlug,
        #[ORM\Column(type: 'datetime_immutable')]
        private \DateTimeImmutable $importedOn,
    ) {
    }

    public static function create(
        int $tcgCollectorCardId,
        int $variantTypeId,
        int $languageId,
        string $name,
        string $setName,
        string $cardNumber,
        ?string $rarity,
        string $variantName,
        TcgRegion $region,
        int $count,
        string $tcgCollectorSlug,
        \DateTimeImmutable $importedOn,
    ): self {
        return new self(
            tcgCollectorCardId: $tcgCollectorCardId,
            variantTypeId: $variantTypeId,
            languageId: $languageId,
            name: $name,
            setName: $setName,
            cardNumber: $cardNumber,
            rarity: $rarity,
            variantName: $variantName,
            region: $region,
            count: $count,
            tcgCollectorSlug: $tcgCollectorSlug,
            importedOn: $importedOn,
        );
    }

    public static function fromState(
        int $tcgCollectorCardId,
        int $variantTypeId,
        int $languageId,
        string $name,
        string $setName,
        string $cardNumber,
        ?string $rarity,
        string $variantName,
        TcgRegion $region,
        int $count,
        string $tcgCollectorSlug,
        \DateTimeImmutable $importedOn,
    ): self {
        return new self(
            tcgCollectorCardId: $tcgCollectorCardId,
            variantTypeId: $variantTypeId,
            languageId: $languageId,
            name: $name,
            setName: $setName,
            cardNumber: $cardNumber,
            rarity: $rarity,
            variantName: $variantName,
            region: $region,
            count: $count,
            tcgCollectorSlug: $tcgCollectorSlug,
            importedOn: $importedOn,
        );
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

    public function getRarity(): ?string
    {
        return $this->rarity;
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

    public function getTcgCollectorSlug(): string
    {
        return $this->tcgCollectorSlug;
    }

    public function getImportedOn(): \DateTimeImmutable
    {
        return $this->importedOn;
    }
}
