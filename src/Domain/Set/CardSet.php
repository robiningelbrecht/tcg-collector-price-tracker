<?php

declare(strict_types=1);

namespace App\Domain\Set;

use App\Domain\TcgDex\TcgDexLanguage;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'CardSet')]
#[ORM\Index(name: 'CardSet_name', columns: ['name'])]
final readonly class CardSet
{
    /**
     * @param array<string, mixed> $data
     */
    private function __construct(
        #[ORM\Id, ORM\Column(type: 'string', enumType: TcgDexLanguage::class)]
        private TcgDexLanguage $language,
        #[ORM\Id, ORM\Column(type: 'string')]
        private string $cardSetId,
        #[ORM\Column(type: 'string')]
        private string $name,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $serieId,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $serieName,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $releaseDate,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $logo,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $symbol,
        #[ORM\Column(type: 'integer', nullable: true)]
        private ?int $cardCountOfficial,
        #[ORM\Column(type: 'integer', nullable: true)]
        private ?int $cardCountTotal,
        #[ORM\Column(type: 'json')]
        private array $data,
        #[ORM\Column(type: 'datetime_immutable', nullable: true)]
        private ?\DateTimeImmutable $detailsImportedOn,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromTcgDex(
        TcgDexLanguage $language,
        array $data,
        ?\DateTimeImmutable $detailsImportedOn,
    ): self {
        unset($data['cards']);
        /** @var array{id?: string, name?: string} $serie */
        $serie = is_array($data['serie'] ?? null) ? $data['serie'] : [];
        /** @var array{official?: int, total?: int} $cardCount */
        $cardCount = is_array($data['cardCount'] ?? null) ? $data['cardCount'] : [];

        return new self(
            language: $language,
            cardSetId: self::stringOrNull($data['id'] ?? null) ?? '',
            name: self::stringOrNull($data['name'] ?? null) ?? '',
            serieId: $serie['id'] ?? null,
            serieName: $serie['name'] ?? null,
            releaseDate: self::stringOrNull($data['releaseDate'] ?? null),
            logo: self::stringOrNull($data['logo'] ?? null),
            symbol: self::stringOrNull($data['symbol'] ?? null),
            cardCountOfficial: $cardCount['official'] ?? null,
            cardCountTotal: $cardCount['total'] ?? null,
            data: $data,
            detailsImportedOn: $detailsImportedOn,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromState(
        TcgDexLanguage $language,
        string $cardSetId,
        string $name,
        ?string $serieId,
        ?string $serieName,
        ?string $releaseDate,
        ?string $logo,
        ?string $symbol,
        ?int $cardCountOfficial,
        ?int $cardCountTotal,
        array $data,
        ?\DateTimeImmutable $detailsImportedOn,
    ): self {
        return new self(
            language: $language,
            cardSetId: $cardSetId,
            name: $name,
            serieId: $serieId,
            serieName: $serieName,
            releaseDate: $releaseDate,
            logo: $logo,
            symbol: $symbol,
            cardCountOfficial: $cardCountOfficial,
            cardCountTotal: $cardCountTotal,
            data: $data,
            detailsImportedOn: $detailsImportedOn,
        );
    }

    private static function stringOrNull(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    public function getLanguage(): TcgDexLanguage
    {
        return $this->language;
    }

    public function getCardSetId(): string
    {
        return $this->cardSetId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSerieId(): ?string
    {
        return $this->serieId;
    }

    public function getSerieName(): ?string
    {
        return $this->serieName;
    }

    public function getReleaseDate(): ?string
    {
        return $this->releaseDate;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function getCardCountOfficial(): ?int
    {
        return $this->cardCountOfficial;
    }

    public function getCardCountTotal(): ?int
    {
        return $this->cardCountTotal;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getDetailsImportedOn(): ?\DateTimeImmutable
    {
        return $this->detailsImportedOn;
    }

    public function hasDetails(): bool
    {
        return $this->detailsImportedOn instanceof \DateTimeImmutable;
    }
}
