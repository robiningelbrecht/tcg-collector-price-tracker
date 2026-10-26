<?php

declare(strict_types=1);

namespace App\Domain\Collection\Overview;

use App\Infrastructure\ValueObject\Collection;

/**
 * @extends Collection<PricePoint>
 */
final class PricePoints extends Collection
{
    public function getItemClassName(): string
    {
        return PricePoint::class;
    }
}
