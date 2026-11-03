<?php

declare(strict_types=1);

namespace App\Domain\Sealed;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'OwnedSealedProduct')]
final readonly class OwnedSealedProduct
{
    private function __construct(
        #[ORM\Id, ORM\GeneratedValue(strategy: 'IDENTITY'), ORM\Column(type: 'integer')]
        private int $productId,
        #[ORM\Column(type: 'integer')]
        private int $quantity,
        #[ORM\Column(type: 'float', nullable: true)]
        private ?float $paidPrice,
        #[ORM\Column(type: 'datetime_immutable')]
        private \DateTimeImmutable $addedOn,
    ) {
    }

    public static function create(
        int $productId,
        int $quantity,
        ?float $paidPrice,
        \DateTimeImmutable $addedOn,
    ): self {
        return new self(
            productId: $productId,
            quantity: $quantity,
            paidPrice: $paidPrice,
            addedOn: $addedOn,
        );
    }

    public static function fromState(
        int $productId,
        int $quantity,
        ?float $paidPrice,
        \DateTimeImmutable $addedOn,
    ): self {
        return new self(
            productId: $productId,
            quantity: $quantity,
            paidPrice: $paidPrice,
            addedOn: $addedOn,
        );
    }

    public function withQuantity(int $quantity): self
    {
        return new self(
            productId: $this->productId,
            quantity: $quantity,
            paidPrice: $this->paidPrice,
            addedOn: $this->addedOn,
        );
    }

    public function withPaidPrice(?float $paidPrice): self
    {
        return new self(
            productId: $this->productId,
            quantity: $this->quantity,
            paidPrice: $paidPrice,
            addedOn: $this->addedOn,
        );
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getPaidPrice(): ?float
    {
        return $this->paidPrice;
    }

    public function getAddedOn(): \DateTimeImmutable
    {
        return $this->addedOn;
    }
}
