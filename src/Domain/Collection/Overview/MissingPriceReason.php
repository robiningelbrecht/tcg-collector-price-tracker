<?php

declare(strict_types=1);

namespace App\Domain\Collection\Overview;

enum MissingPriceReason: string
{
    case CARD_NOT_PRICED = 'cardNotPriced';
    case VARIANT_NOT_PRICED = 'variantNotPriced';

    public function getLabel(): string
    {
        return match ($this) {
            self::CARD_NOT_PRICED => 'TCGdex has no prices for this card',
            self::VARIANT_NOT_PRICED => 'TCGdex has no price for this variant',
        };
    }
}
