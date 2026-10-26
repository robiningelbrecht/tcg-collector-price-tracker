<?php

declare(strict_types=1);

namespace App\Domain\Collection;

final readonly class CardMatch
{
    private function __construct(
        private ?OwnedCard $ownedCard,
        private ?string $reason,
    ) {
    }

    public static function matched(OwnedCard $ownedCard): self
    {
        return new self(ownedCard: $ownedCard, reason: null);
    }

    public static function unmatched(string $reason): self
    {
        return new self(ownedCard: null, reason: $reason);
    }

    public function getOwnedCard(): ?OwnedCard
    {
        return $this->ownedCard;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }
}
