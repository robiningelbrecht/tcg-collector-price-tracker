<?php

declare(strict_types=1);

namespace App\Domain\Binder\Overview;

interface BinderOverviewQuery
{
    public function find(): BinderOverviews;
}
