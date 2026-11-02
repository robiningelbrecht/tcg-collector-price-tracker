<?php

declare(strict_types=1);

namespace App\Domain\Binder;

use App\Infrastructure\ValueObject\Collection;

/**
 * @extends Collection<Binder>
 */
final class Binders extends Collection
{
    public function getItemClassName(): string
    {
        return Binder::class;
    }
}
