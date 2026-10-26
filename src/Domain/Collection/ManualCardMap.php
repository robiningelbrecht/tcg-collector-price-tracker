<?php

declare(strict_types=1);

namespace App\Domain\Collection;

final readonly class ManualCardMap
{
    public function find(int $tcgCollectorCardId): ?string
    {
        return $this->map()[$tcgCollectorCardId] ?? null;
    }

    /**
     * @return array<int, string>
     */
    private function map(): array
    {
        return [
        ];
    }
}
