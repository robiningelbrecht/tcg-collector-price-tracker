<?php

declare(strict_types=1);

namespace App\Domain\Sealed\Owned;

use App\Infrastructure\ValueObject\Collection;

/**
 * @extends Collection<OwnedSealedProduct>
 */
final class OwnedSealedProducts extends Collection
{
    public function getItemClassName(): string
    {
        return OwnedSealedProduct::class;
    }
}
