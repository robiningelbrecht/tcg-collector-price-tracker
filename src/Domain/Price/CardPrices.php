<?php

declare(strict_types=1);

namespace App\Domain\Price;

use App\Infrastructure\ValueObject\Collection;

/**
 * @extends Collection<CardPrice>
 */
final class CardPrices extends Collection
{
    public function getItemClassName(): string
    {
        return CardPrice::class;
    }
}
