<?php

declare(strict_types=1);

namespace App\Domain\Set;

final readonly class SetNameMap
{
    /** @var array<string, string> */
    private const array SET_NAME_TO_CARD_SET_ID = [
        'Scarlet & Violet 151' => 'sv03.5',
    ];

    public function resolve(string $setName, CardSets $cardSets): ?string
    {
        if (isset(self::SET_NAME_TO_CARD_SET_ID[$setName])) {
            return self::SET_NAME_TO_CARD_SET_ID[$setName];
        }

        if (($cardSet = $cardSets->findByName($setName)) instanceof CardSet) {
            return $cardSet->getCardSetId();
        }

        if (str_starts_with($setName, 'EX ')) {
            return $cardSets->findByName(substr($setName, 3))?->getCardSetId();
        }

        return null;
    }
}
