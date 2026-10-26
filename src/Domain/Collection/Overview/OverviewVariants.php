<?php

declare(strict_types=1);

namespace App\Domain\Collection\Overview;

use App\Infrastructure\ValueObject\Collection;

/**
 * @extends Collection<OverviewVariant>
 */
final class OverviewVariants extends Collection
{
    public function getItemClassName(): string
    {
        return OverviewVariant::class;
    }
}
