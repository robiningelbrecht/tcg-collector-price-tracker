<?php

declare(strict_types=1);

namespace App\Domain\Set;

use App\Infrastructure\ValueObject\Collection;

/**
 * @extends Collection<CardSet>
 */
final class CardSets extends Collection
{
    public function getItemClassName(): string
    {
        return CardSet::class;
    }

    public function findById(string $cardSetId): ?CardSet
    {
        return $this->find(fn (CardSet $cardSet): bool => $cardSet->getCardSetId() === $cardSetId);
    }
}
