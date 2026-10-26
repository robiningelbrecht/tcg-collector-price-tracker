<?php

declare(strict_types=1);

namespace App\Domain\Price;

use App\Domain\TcgDex\TcgDexLanguage;

interface CardPriceRepository
{
    public function upsert(CardPrice $cardPrice): void;

    public function deleteForCardOnDate(TcgDexLanguage $language, string $cardId, \DateTimeImmutable $date): void;
}
