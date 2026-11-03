<?php

declare(strict_types=1);

namespace App\Domain\Sealed;

use App\Infrastructure\ValueObject\Collection;

/**
 * @extends Collection<SealedProduct>
 */
final class SealedProducts extends Collection
{
    public function getItemClassName(): string
    {
        return SealedProduct::class;
    }
}
