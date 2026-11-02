<?php

declare(strict_types=1);

namespace App\Domain\Binder;

use App\Domain\TcgDex\TcgDexLanguage;

interface BinderRepository
{
    public function save(Binder $binder): void;

    public function delete(Binder $binder): void;

    public function find(TcgDexLanguage $language, string $cardSetId): ?Binder;

    public function findAll(): Binders;
}
