<?php

declare(strict_types=1);

namespace App\Domain\Binder\Page\UploadBinderPagePhoto;

use App\Domain\TcgDex\TcgDexLanguage;
use App\Infrastructure\CQRS\Command\Deserialize\CouldNotDeserializeCommand;
use App\Infrastructure\CQRS\Command\Deserialize\DeserializableCommand;
use App\Infrastructure\CQRS\Command\Deserialize\ProvidesCommandName;
use App\Infrastructure\CQRS\Command\DomainCommand;

final readonly class UploadBinderPagePhoto extends DomainCommand implements DeserializableCommand
{
    use ProvidesCommandName;

    public const int MAX_PHOTO_SIZE = 20 * 1024 * 1024;

    private function __construct(
        private TcgDexLanguage $language,
        private string $cardSetId,
        private int $pageNumber,
        private string $photo,
        private \DateTimeImmutable $takenOn,
    ) {
    }

    public static function fromPayload(array $payload): self
    {
        $setId = $payload['setId'] ?? null;
        if (!is_string($setId) || !str_contains($setId, '/')) {
            throw CouldNotDeserializeCommand::invalidPayload('A binder is required.');
        }

        [$language, $cardSetId] = explode('/', trim($setId), 2);
        $language = TcgDexLanguage::tryFrom($language);
        if (null === $language || '' === $cardSetId) {
            throw CouldNotDeserializeCommand::invalidPayload('A binder is required.');
        }

        $pageNumber = filter_var($payload['pageNumber'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (false === $pageNumber) {
            throw CouldNotDeserializeCommand::invalidPayload('A page number is required.');
        }

        $photo = $payload['photo'] ?? null;
        $photo = is_string($photo) ? base64_decode(preg_replace('/^data:[^,]*,/', '', $photo) ?? '', true) : false;
        if (false === $photo || '' === $photo) {
            throw CouldNotDeserializeCommand::invalidPayload('A photo is required.');
        }
        if (strlen($photo) > self::MAX_PHOTO_SIZE) {
            throw CouldNotDeserializeCommand::invalidPayload('The photo is too large. The maximum is 20 MB.');
        }

        return new self(
            language: $language,
            cardSetId: $cardSetId,
            pageNumber: $pageNumber,
            photo: $photo,
            takenOn: self::takenOn($payload['takenOn'] ?? null),
        );
    }

    private static function takenOn(mixed $milliseconds): \DateTimeImmutable
    {
        $now = new \DateTimeImmutable();
        $timestamp = filter_var($milliseconds, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (false === $timestamp) {
            return $now;
        }

        return min(new \DateTimeImmutable('@'.intdiv($timestamp, 1000))->setTimezone($now->getTimezone()), $now);
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

    public function getPhoto(): string
    {
        return $this->photo;
    }

    public function getTakenOn(): \DateTimeImmutable
    {
        return $this->takenOn;
    }
}
