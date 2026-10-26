<?php

declare(strict_types=1);

namespace App\Domain\Set;

interface CardSetRepository
{
    public function upsert(CardSet $cardSet): void;
}
