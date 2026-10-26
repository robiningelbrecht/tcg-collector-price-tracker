<?php

declare(strict_types=1);

namespace App\Domain\Collection\Overview;

use App\Infrastructure\ValueObject\Collection;

/**
 * @extends Collection<OverviewCard>
 */
final class OverviewCards extends Collection
{
    public function getItemClassName(): string
    {
        return OverviewCard::class;
    }
}
