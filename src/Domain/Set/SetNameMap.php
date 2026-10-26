<?php

declare(strict_types=1);

namespace App\Domain\Set;

use App\Domain\TcgDex\TcgDexLanguage;

final readonly class SetNameMap
{
    /** @var array<string, string> */
    private const array ENGLISH = [
        'Scarlet & Violet 151' => 'sv03.5',
    ];

    /** @var array<string, string> */
    private const array JAPANESE = [
        'Expansion Pack' => 'PMCG1',
        'Mystery of the Fossils' => 'PMCG3',
        'Team Rocket' => 'PMCG4',
        'Challenge from the Darkness' => 'PMCG6',
        'Gold, Silver, to a New World...' => 'neo1',
        'Crossing the Ruins...' => 'neo2',
        'Awakening Legends' => 'neo3',
    ];

    public function resolve(string $setName, TcgDexLanguage $language, CardSets $cardSets): ?string
    {
        $map = match ($language) {
            TcgDexLanguage::ENGLISH => self::ENGLISH,
            TcgDexLanguage::JAPANESE => self::JAPANESE,
        };

        if (isset($map[$setName])) {
            return $map[$setName];
        }

        if (($cardSet = $cardSets->findByName($setName)) instanceof CardSet) {
            return $cardSet->getCardSetId();
        }

        if (TcgDexLanguage::ENGLISH === $language && str_starts_with($setName, 'EX ')) {
            return $cardSets->findByName(substr($setName, 3))?->getCardSetId();
        }

        return null;
    }
}
