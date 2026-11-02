<?php

declare(strict_types=1);

namespace App\Domain\Binder;

use App\Domain\TcgDex\TcgDexLanguage;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'Binder')]
final readonly class Binder
{
    private function __construct(
        #[ORM\Id, ORM\Column(type: 'string', enumType: TcgDexLanguage::class)]
        private TcgDexLanguage $language,
        #[ORM\Id, ORM\Column(type: 'string')]
        private string $cardSetId,
        #[ORM\Column(type: 'integer')]
        private int $pageCount,
        #[ORM\Column(type: 'integer')]
        private int $pocketsPerPage,
        #[ORM\Column(type: 'datetime_immutable')]
        private \DateTimeImmutable $createdOn,
    ) {
    }

    public static function create(
        TcgDexLanguage $language,
        string $cardSetId,
        int $pageCount,
        int $pocketsPerPage,
        \DateTimeImmutable $createdOn,
    ): self {
        return new self(
            language: $language,
            cardSetId: $cardSetId,
            pageCount: $pageCount,
            pocketsPerPage: $pocketsPerPage,
            createdOn: $createdOn,
        );
    }

    public static function fromState(
        TcgDexLanguage $language,
        string $cardSetId,
        int $pageCount,
        int $pocketsPerPage,
        \DateTimeImmutable $createdOn,
    ): self {
        return new self(
            language: $language,
            cardSetId: $cardSetId,
            pageCount: $pageCount,
            pocketsPerPage: $pocketsPerPage,
            createdOn: $createdOn,
        );
    }

    public function withPageCount(int $pageCount): self
    {
        return new self(
            language: $this->language,
            cardSetId: $this->cardSetId,
            pageCount: $pageCount,
            pocketsPerPage: $this->pocketsPerPage,
            createdOn: $this->createdOn,
        );
    }

    public function withPocketsPerPage(int $pocketsPerPage): self
    {
        return new self(
            language: $this->language,
            cardSetId: $this->cardSetId,
            pageCount: $this->pageCount,
            pocketsPerPage: $pocketsPerPage,
            createdOn: $this->createdOn,
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

    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    public function getPocketsPerPage(): int
    {
        return $this->pocketsPerPage;
    }

    public function getCreatedOn(): \DateTimeImmutable
    {
        return $this->createdOn;
    }
}
