<?php

declare(strict_types=1);

namespace App\Domain\Collection;

use App\Domain\TcgDex\TcgDexLanguage;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'OwnedCard')]
#[ORM\Index(name: 'OwnedCard_card', columns: ['language', 'cardId'])]
#[ORM\Index(name: 'OwnedCard_cardSet', columns: ['language', 'cardSetId'])]
final readonly class OwnedCard
{
    /**
     * @param array<string, mixed> $data
     */
    private function __construct(
        #[ORM\Id, ORM\Column(type: 'integer')]
        private int $tcgCollectorCardId,
        #[ORM\Id, ORM\Column(type: 'integer')]
        private int $variantTypeId,
        #[ORM\Id, ORM\Column(type: 'integer')]
        private int $languageId,
        #[ORM\Column(type: 'string')]
        private string $variantName,
        #[ORM\Column(type: 'integer')]
        private int $count,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $priceVariantId,
        #[ORM\Column(type: 'string', enumType: TcgDexLanguage::class)]
        private TcgDexLanguage $language,
        #[ORM\Column(type: 'string')]
        private string $cardId,
        #[ORM\Column(type: 'string')]
        private string $cardSetId,
        #[ORM\Column(type: 'string')]
        private string $localId,
        #[ORM\Column(type: 'string')]
        private string $name,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $category,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $rarity,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $illustrator,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $image,
        #[ORM\Column(type: 'integer', nullable: true)]
        private ?int $hp,
        #[ORM\Column(type: 'json')]
        private array $data,
        #[ORM\Column(type: 'datetime_immutable')]
        private \DateTimeImmutable $importedOn,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromState(
        int $tcgCollectorCardId,
        int $variantTypeId,
        int $languageId,
        string $variantName,
        int $count,
        ?string $priceVariantId,
        TcgDexLanguage $language,
        string $cardId,
        string $cardSetId,
        string $localId,
        string $name,
        ?string $category,
        ?string $rarity,
        ?string $illustrator,
        ?string $image,
        ?int $hp,
        array $data,
        \DateTimeImmutable $importedOn,
    ): self {
        return new self(
            tcgCollectorCardId: $tcgCollectorCardId,
            variantTypeId: $variantTypeId,
            languageId: $languageId,
            variantName: $variantName,
            count: $count,
            priceVariantId: $priceVariantId,
            language: $language,
            cardId: $cardId,
            cardSetId: $cardSetId,
            localId: $localId,
            name: $name,
            category: $category,
            rarity: $rarity,
            illustrator: $illustrator,
            image: $image,
            hp: $hp,
            data: $data,
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

    public function getVariantName(): string
    {
        return $this->variantName;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getPriceVariantId(): ?string
    {
        return $this->priceVariantId;
    }

    public function getLanguage(): TcgDexLanguage
    {
        return $this->language;
    }

    public function getCardId(): string
    {
        return $this->cardId;
    }

    public function getCardSetId(): string
    {
        return $this->cardSetId;
    }

    public function getLocalId(): string
    {
        return $this->localId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getRarity(): ?string
    {
        return $this->rarity;
    }

    public function getIllustrator(): ?string
    {
        return $this->illustrator;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getHp(): ?int
    {
        return $this->hp;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getImportedOn(): \DateTimeImmutable
    {
        return $this->importedOn;
    }
}
