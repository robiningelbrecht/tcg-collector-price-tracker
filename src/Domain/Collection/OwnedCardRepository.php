<?php

declare(strict_types=1);

namespace App\Domain\Collection;

use App\Domain\TcgDex\TcgDexLanguage;

interface OwnedCardRepository
{
    public function save(OwnedCard $ownedCard): void;

    public function delete(OwnedCard $ownedCard): void;

    public function find(string $cardId, string $priceVariantId): ?OwnedCard;

    public function findOneByCardId(TcgDexLanguage $language, string $cardId): ?OwnedCard;

    public function findAll(): OwnedCards;
}
