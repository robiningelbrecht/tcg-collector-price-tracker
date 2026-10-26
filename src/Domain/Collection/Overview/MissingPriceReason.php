<?php

declare(strict_types=1);

namespace App\Domain\Collection\Overview;

enum MissingPriceReason: string
{
    case NO_PRICE_VARIANT = 'noPriceVariant';
    case CARD_NOT_PRICED = 'cardNotPriced';
    case VARIANT_NOT_PRICED = 'variantNotPriced';

    public function getLabel(): string
    {
        return match ($this) {
            self::NO_PRICE_VARIANT => 'No price variant chosen yet',
            self::CARD_NOT_PRICED => 'TCGdex has no prices for this card',
            self::VARIANT_NOT_PRICED => 'TCGdex has no price for this variant',
        };
    }
}
