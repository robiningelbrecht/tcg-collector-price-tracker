<?php

declare(strict_types=1);

namespace App\Domain\Collection\UpdateOwnedCard;

use App\Infrastructure\CQRS\Command\Deserialize\CouldNotDeserializeCommand;
use App\Infrastructure\CQRS\Command\Deserialize\DeserializableCommand;
use App\Infrastructure\CQRS\Command\Deserialize\ProvidesCommandName;
use App\Infrastructure\CQRS\Command\DomainCommand;

final readonly class UpdateOwnedCard extends DomainCommand implements DeserializableCommand
{
    use ProvidesCommandName;

    private function __construct(
        private string $cardId,
        private string $variantId,
        private string $newVariantId,
        private int $count,
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

        $newVariantId = $payload['newVariantId'] ?? null;
        if (!is_string($newVariantId) || '' === trim($newVariantId)) {
            throw CouldNotDeserializeCommand::invalidPayload('Pick a variant.');
        }

        $count = filter_var($payload['count'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (false === $count) {
            throw CouldNotDeserializeCommand::invalidPayload('The count must be a whole number of at least 1.');
        }

        return new self(
            cardId: trim($cardId),
            variantId: trim($variantId),
            newVariantId: trim($newVariantId),
            count: $count,
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

    public function getNewVariantId(): string
    {
        return $this->newVariantId;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
