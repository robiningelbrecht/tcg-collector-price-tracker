<?php

declare(strict_types=1);

namespace App\Domain\Binder\Overview;

use App\Domain\TcgDex\TcgDexLanguage;

final readonly class BinderOverview
{
    private function __construct(
        private TcgDexLanguage $language,
        private string $cardSetId,
        private string $setName,
        private ?string $serieName,
        private ?string $logo,
        private int $pageCount,
        private int $pocketsPerPage,
        private int $photographedPageCount,
        private ?\DateTimeImmutable $lastTakenOn,
        private ?\DateTimeImmutable $oldestTakenOn,
    ) {
    }

    public static function fromState(
        TcgDexLanguage $language,
        string $cardSetId,
        string $setName,
        ?string $serieName,
        ?string $logo,
        int $pageCount,
        int $pocketsPerPage,
        int $photographedPageCount,
        ?\DateTimeImmutable $lastTakenOn,
        ?\DateTimeImmutable $oldestTakenOn,
    ): self {
        return new self(
            language: $language,
            cardSetId: $cardSetId,
            setName: $setName,
            serieName: $serieName,
            logo: $logo,
            pageCount: $pageCount,
            pocketsPerPage: $pocketsPerPage,
            photographedPageCount: $photographedPageCount,
            lastTakenOn: $lastTakenOn,
            oldestTakenOn: $oldestTakenOn,
        );
    }

    public function getLanguage(): TcgDexLanguage
    {
        return $this->language;
    }

    public function getCardSetId(): string
    {
        return $this->cardSetId;
    }

    public function getSetName(): string
    {
        return $this->setName;
    }

    public function getSerieName(): ?string
    {
        return $this->serieName;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    public function getPocketsPerPage(): int
    {
        return $this->pocketsPerPage;
    }

    public function getPocketCount(): int
    {
        return $this->pageCount * $this->pocketsPerPage;
    }

    public function getPhotographedPageCount(): int
    {
        return $this->photographedPageCount;
    }

    public function getMissingPageCount(): int
    {
        return $this->pageCount - $this->photographedPageCount;
    }

    public function getPhotoCoverage(): float
    {
        return 0 === $this->pageCount ? 0.0 : $this->photographedPageCount / $this->pageCount;
    }

    public function isFullyPhotographed(): bool
    {
        return 0 === $this->getMissingPageCount();
    }

    public function getLastTakenOn(): ?\DateTimeImmutable
    {
        return $this->lastTakenOn;
    }

    public function getOldestTakenOn(): ?\DateTimeImmutable
    {
        return $this->oldestTakenOn;
    }
}
