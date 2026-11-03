<?php

declare(strict_types=1);

namespace App\Domain\Sealed\Owned\RemoveOwnedSealedProduct;

use App\Infrastructure\CQRS\Command\Deserialize\CouldNotDeserializeCommand;
use App\Infrastructure\CQRS\Command\Deserialize\DeserializableCommand;
use App\Infrastructure\CQRS\Command\Deserialize\ProvidesCommandName;
use App\Infrastructure\CQRS\Command\DomainCommand;

final readonly class RemoveOwnedSealedProduct extends DomainCommand implements DeserializableCommand
{
    use ProvidesCommandName;

    private function __construct(
        private int $productId,
    ) {
    }

    public static function fromPayload(array $payload): self
    {
        $productId = filter_var($payload['productId'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (false === $productId) {
            throw CouldNotDeserializeCommand::invalidPayload('A product is required.');
        }

        return new self(
            productId: $productId,
        );
    }

    public function getProductId(): int
    {
        return $this->productId;
    }
}
