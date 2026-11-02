<?php

declare(strict_types=1);

namespace App\Domain\Binder\Overview;

use App\Infrastructure\ValueObject\Collection;

/**
 * @extends Collection<BinderOverview>
 */
final class BinderOverviews extends Collection
{
    public function getItemClassName(): string
    {
        return BinderOverview::class;
    }

    public function getPageCount(): int
    {
        return (int) $this->sum(fn (BinderOverview $binder): int => $binder->getPageCount());
    }

    public function getPhotographedPageCount(): int
    {
        return (int) $this->sum(fn (BinderOverview $binder): int => $binder->getPhotographedPageCount());
    }

    public function getMissingPageCount(): int
    {
        return $this->getPageCount() - $this->getPhotographedPageCount();
    }

    public function getPhotoCoverage(): float
    {
        $pageCount = $this->getPageCount();

        return 0 === $pageCount ? 0.0 : $this->getPhotographedPageCount() / $pageCount;
    }

    public function getFullyPhotographedCount(): int
    {
        return count($this->filter(fn (BinderOverview $binder): bool => $binder->isFullyPhotographed()));
    }

    public function getLastTakenOn(): ?\DateTimeImmutable
    {
        $dates = array_filter($this->map(fn (BinderOverview $binder): ?\DateTimeImmutable => $binder->getLastTakenOn()));

        return [] === $dates ? null : max($dates);
    }

    public function getOldestTakenOn(): ?\DateTimeImmutable
    {
        $dates = array_filter($this->map(fn (BinderOverview $binder): ?\DateTimeImmutable => $binder->getOldestTakenOn()));

        return [] === $dates ? null : min($dates);
    }
}
