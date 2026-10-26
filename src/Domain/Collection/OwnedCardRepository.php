<?php

declare(strict_types=1);

namespace App\Domain\Collection;

interface OwnedCardRepository
{
    public function upsert(OwnedCard $ownedCard): void;

    public function findAll(): OwnedCards;
}
