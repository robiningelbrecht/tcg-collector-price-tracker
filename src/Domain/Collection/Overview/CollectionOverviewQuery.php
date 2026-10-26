<?php

declare(strict_types=1);

namespace App\Domain\Collection\Overview;

interface CollectionOverviewQuery
{
    public function find(): OverviewCards;
}
