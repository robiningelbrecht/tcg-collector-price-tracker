<?php

declare(strict_types=1);

namespace App\Domain\Binder\Page;

use App\Domain\Binder\Binder;

interface BinderPageRepository
{
    public function save(BinderPage $page): void;

    public function delete(BinderPage $page): void;

    public function find(Binder $binder, int $pageNumber): ?BinderPage;

    public function findByBinder(Binder $binder): BinderPages;
}
