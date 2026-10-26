<?php

declare(strict_types=1);

namespace App\Domain\Set;

use App\Domain\TcgDex\TcgDexLanguage;

interface CardSetRepository
{
    public function upsert(CardSet $cardSet): void;

    public function find(TcgDexLanguage $language, string $cardSetId): ?CardSet;
}
