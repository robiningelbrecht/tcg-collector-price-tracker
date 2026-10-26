<?php

declare(strict_types=1);

namespace App\Domain\TcgCollector;

use App\Infrastructure\ValueObject\Collection;

/**
 * @extends Collection<CollectionCard>
 */
final class CollectionCards extends Collection
{
    public function getItemClassName(): string
    {
        return CollectionCard::class;
    }
}
