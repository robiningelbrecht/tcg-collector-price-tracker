<?php

declare(strict_types=1);

namespace App\Domain\Price;

interface CardPriceRepository
{
    public function upsert(CardPrice $cardPrice): void;
}
