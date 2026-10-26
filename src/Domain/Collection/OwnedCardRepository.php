<?php

declare(strict_types=1);

namespace App\Domain\Collection;

use App\Domain\TcgDex\TcgDexLanguage;

interface OwnedCardRepository
{
    public function upsert(OwnedCard $ownedCard): void;

    public function find(int $tcgCollectorCardId, int $variantTypeId, int $languageId): ?OwnedCard;

    public function findOneByCardId(TcgDexLanguage $language, string $cardId): ?OwnedCard;

    public function findAll(): OwnedCards;
}
