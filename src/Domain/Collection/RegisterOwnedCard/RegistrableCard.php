<?php

declare(strict_types=1);

namespace App\Domain\Collection\RegisterOwnedCard;

use App\Domain\Collection\OwnedCard;
use App\Domain\Collection\OwnedCards;

final readonly class RegistrableCard
{
    private function __construct(
        private string $cardId,
        private string $localId,
        private string $name,
        private ?string $image,
        private bool $tracked,
    ) {
    }

    /**
     * @param array<string, mixed> $set
     *
     * @return list<self>
     */
    public static function listFromTcgDexSet(array $set, OwnedCards $ownedCards): array
    {
        $trackedCardIds = array_flip($ownedCards->map(fn (OwnedCard $ownedCard): string => $ownedCard->getCardId()));

        $cards = [];
        foreach (is_array($set['cards'] ?? null) ? $set['cards'] : [] as $card) {
            if (!is_array($card) || !is_string($card['id'] ?? null)) {
                continue;
            }

            $cards[] = new self(
                cardId: $card['id'],
                localId: is_scalar($card['localId'] ?? null) ? (string) $card['localId'] : '',
                name: is_string($card['name'] ?? null) ? $card['name'] : $card['id'],
                image: is_string($card['image'] ?? null) ? $card['image'] : null,
                tracked: isset($trackedCardIds[$card['id']]),
            );
        }

        return $cards;
    }

    public function getCardId(): string
    {
        return $this->cardId;
    }

    public function getLocalId(): string
    {
        return $this->localId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function isTracked(): bool
    {
        return $this->tracked;
    }
}
