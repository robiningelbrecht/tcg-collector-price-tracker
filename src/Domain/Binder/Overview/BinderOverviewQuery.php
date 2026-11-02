<?php

declare(strict_types=1);

namespace App\Domain\Binder\Overview;

interface BinderOverviewQuery
{
    public function find(): BinderOverviews;

    /**
     * @return list<array{label: string, sets: list<array{id: string, label: string}>}>
     */
    public function findOwnedSetsWithoutBinder(): array;
}
