<?php

declare(strict_types=1);

namespace App\Domain\Collection;

use App\Infrastructure\ValueObject\Collection;

/**
 * @extends Collection<OwnedCard>
 */
final class OwnedCards extends Collection
{
    public function getItemClassName(): string
    {
        return OwnedCard::class;
    }
}
