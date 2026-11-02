<?php

declare(strict_types=1);

namespace App\Domain\Binder\Page;

use App\Infrastructure\ValueObject\Collection;

/**
 * @extends Collection<BinderPage>
 */
final class BinderPages extends Collection
{
    public function getItemClassName(): string
    {
        return BinderPage::class;
    }

    public function findByPageNumber(int $pageNumber): ?BinderPage
    {
        return $this->find(fn (BinderPage $page): bool => $page->getPageNumber() === $pageNumber);
    }
}
