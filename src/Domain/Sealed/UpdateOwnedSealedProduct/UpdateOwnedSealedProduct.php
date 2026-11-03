<?php

declare(strict_types=1);

namespace App\Domain\Sealed\UpdateOwnedSealedProduct;

use App\Domain\Sealed\PaidPrice;
use App\Infrastructure\CQRS\Command\Deserialize\CouldNotDeserializeCommand;
use App\Infrastructure\CQRS\Command\Deserialize\DeserializableCommand;
use App\Infrastructure\CQRS\Command\Deserialize\ProvidesCommandName;
use App\Infrastructure\CQRS\Command\DomainCommand;

final readonly class UpdateOwnedSealedProduct extends DomainCommand implements DeserializableCommand
{
    use ProvidesCommandName;

    private function __construct(
        private int $productId,
        private int $quantity,
        private ?float $paidPrice,
    ) {
    }

    public static function fromPayload(array $payload): self
    {
        $productId = filter_var($payload['productId'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (false === $productId) {
            throw CouldNotDeserializeCommand::invalidPayload('A product is required.');
        }

        $quantity = filter_var($payload['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (false === $quantity) {
            throw CouldNotDeserializeCommand::invalidPayload('The quantity must be a whole number of at least 1.');
        }

        return new self(
            productId: $productId,
            quantity: $quantity,
            paidPrice: PaidPrice::fromPayload($payload['paidPrice'] ?? null),
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
}
