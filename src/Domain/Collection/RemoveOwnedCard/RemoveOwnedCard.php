<?php

declare(strict_types=1);

namespace App\Domain\Collection\RemoveOwnedCard;

use App\Infrastructure\CQRS\Command\Deserialize\CouldNotDeserializeCommand;
use App\Infrastructure\CQRS\Command\Deserialize\DeserializableCommand;
use App\Infrastructure\CQRS\Command\Deserialize\ProvidesCommandName;
use App\Infrastructure\CQRS\Command\DomainCommand;

final readonly class RemoveOwnedCard extends DomainCommand implements DeserializableCommand
{
    use ProvidesCommandName;

    private function __construct(
        private string $cardId,
        private string $variantId,
    ) {
    }

    public static function fromPayload(array $payload): self
    {
        $cardId = $payload['cardId'] ?? null;
        if (!is_string($cardId) || '' === trim($cardId)) {
            throw CouldNotDeserializeCommand::invalidPayload('A card is required.');
        }

        $variantId = $payload['variantId'] ?? null;
        if (!is_string($variantId) || '' === trim($variantId)) {
            throw CouldNotDeserializeCommand::invalidPayload('A variant is required.');
        }

        return new self(
            cardId: trim($cardId),
            variantId: trim($variantId),
        );
    }

    public function getCardId(): string
    {
        return $this->cardId;
    }

    public function getVariantId(): string
    {
        return $this->variantId;
    }
}
