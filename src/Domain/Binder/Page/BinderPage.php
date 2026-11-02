<?php

declare(strict_types=1);

namespace App\Domain\Binder\Page;

use App\Domain\TcgDex\TcgDexLanguage;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'BinderPage')]
final readonly class BinderPage
{
    private function __construct(
        #[ORM\Id, ORM\Column(type: 'string', enumType: TcgDexLanguage::class)]
        private TcgDexLanguage $language,
        #[ORM\Id, ORM\Column(type: 'string')]
        private string $cardSetId,
        #[ORM\Id, ORM\Column(type: 'integer')]
        private int $pageNumber,
        #[ORM\Column(type: 'string')]
        private string $fileName,
        #[ORM\Column(type: 'datetime_immutable')]
        private \DateTimeImmutable $takenOn,
        #[ORM\Column(type: 'datetime_immutable')]
        private \DateTimeImmutable $uploadedOn,
    ) {
    }

    public static function create(
        TcgDexLanguage $language,
        string $cardSetId,
        int $pageNumber,
        string $fileName,
        \DateTimeImmutable $takenOn,
        \DateTimeImmutable $uploadedOn,
    ): self {
        return new self(
            language: $language,
            cardSetId: $cardSetId,
            pageNumber: $pageNumber,
            fileName: $fileName,
            takenOn: $takenOn,
            uploadedOn: $uploadedOn,
        );
    }

    public static function fromState(
        TcgDexLanguage $language,
        string $cardSetId,
        int $pageNumber,
        string $fileName,
        \DateTimeImmutable $takenOn,
        \DateTimeImmutable $uploadedOn,
    ): self {
        return new self(
            language: $language,
            cardSetId: $cardSetId,
            pageNumber: $pageNumber,
            fileName: $fileName,
            takenOn: $takenOn,
            uploadedOn: $uploadedOn,
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

    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getTakenOn(): \DateTimeImmutable
    {
        return $this->takenOn;
    }

    public function getUploadedOn(): \DateTimeImmutable
    {
        return $this->uploadedOn;
    }
}
