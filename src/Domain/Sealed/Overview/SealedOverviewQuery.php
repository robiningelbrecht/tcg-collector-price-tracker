<?php

declare(strict_types=1);

namespace App\Domain\Sealed\Overview;

interface SealedOverviewQuery
{
    public function find(): SealedOverviewProducts;
}
