<?php

declare(strict_types=1);

namespace App\Domain\Collection;

final readonly class ManualCardMap
{
    /** @var array<int, string> */
    private const array TCG_COLLECTOR_CARD_ID_TO_TCGDEX_CARD_ID = [
        29415 => 'neo3-011',
        29482 => 'neo2-021',
        29501 => 'neo2-040',
        29589 => 'neo1-072',
        29663 => 'PMCG6-032',
        29685 => 'PMCG6-054',
        29840 => 'PMCG4-017',
        29919 => 'PMCG3-031',
        30005 => 'PMCG1-021',
        30016 => 'PMCG1-032',
    ];

    public function find(int $tcgCollectorCardId): ?string
    {
        return self::TCG_COLLECTOR_CARD_ID_TO_TCGDEX_CARD_ID[$tcgCollectorCardId] ?? null;
    }
}
