<?php

declare(strict_types=1);

namespace App\Domain\Collection;

final readonly class ManualPriceVariantMap
{
    public function find(int $tcgCollectorCardId, int $variantTypeId): ?string
    {
        return $this->map()[self::key($tcgCollectorCardId, $variantTypeId)] ?? null;
    }

    public static function key(int $tcgCollectorCardId, int $variantTypeId): string
    {
        return $tcgCollectorCardId.'-'.$variantTypeId;
    }

    /**
     * @return array<string, string>
     */
    private function map(): array
    {
        return [
        ];
    }
}
