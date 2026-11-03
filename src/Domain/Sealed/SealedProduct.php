<?php

declare(strict_types=1);

namespace App\Domain\Sealed;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'SealedProduct')]
final readonly class SealedProduct
{
    /**
     * @param array<string, mixed> $data
     */
    private function __construct(
        #[ORM\Id, ORM\GeneratedValue(strategy: 'IDENTITY'), ORM\Column(type: 'integer')]
        private int $productId,
        #[ORM\Column(type: 'string')]
        private string $name,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $categoryName,
        #[ORM\Column(type: 'integer', nullable: true)]
        private ?int $expansionId,
        #[ORM\Column(type: 'datetime_immutable', nullable: true)]
        private ?\DateTimeImmutable $addedOn,
        #[ORM\Column(type: 'json')]
        private array $data,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromCardmarket(array $data): self
    {
        $addedOn = $data['dateAdded'] ?? null;

        return new self(
            productId: is_int($data['idProduct'] ?? null) ? $data['idProduct'] : 0,
            name: is_string($data['name'] ?? null) ? $data['name'] : '',
            categoryName: is_string($data['categoryName'] ?? null) ? $data['categoryName'] : null,
            expansionId: is_int($data['idExpansion'] ?? null) && $data['idExpansion'] > 0 ? $data['idExpansion'] : null,
            addedOn: is_string($addedOn) && false !== strtotime($addedOn) ? new \DateTimeImmutable($addedOn) : null,
            data: $data,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromState(
        int $productId,
        string $name,
        ?string $categoryName,
        ?int $expansionId,
        ?\DateTimeImmutable $addedOn,
        array $data,
    ): self {
        return new self(
            productId: $productId,
            name: $name,
            categoryName: $categoryName,
            expansionId: $expansionId,
            addedOn: $addedOn,
            data: $data,
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

    public function getExpansionId(): ?int
    {
        return $this->expansionId;
    }

    public function getAddedOn(): ?\DateTimeImmutable
    {
        return $this->addedOn;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
