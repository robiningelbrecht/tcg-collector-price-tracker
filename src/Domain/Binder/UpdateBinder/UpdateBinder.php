<?php

declare(strict_types=1);

namespace App\Domain\Binder\UpdateBinder;

use App\Domain\TcgDex\TcgDexLanguage;
use App\Infrastructure\CQRS\Command\Deserialize\CouldNotDeserializeCommand;
use App\Infrastructure\CQRS\Command\Deserialize\DeserializableCommand;
use App\Infrastructure\CQRS\Command\Deserialize\ProvidesCommandName;
use App\Infrastructure\CQRS\Command\DomainCommand;

final readonly class UpdateBinder extends DomainCommand implements DeserializableCommand
{
    use ProvidesCommandName;

    private function __construct(
        private TcgDexLanguage $language,
        private string $cardSetId,
        private int $pageCount,
        private int $pocketsPerPage,
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

        $pageCount = filter_var($payload['pageCount'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 500]]);
        if (false === $pageCount) {
            throw CouldNotDeserializeCommand::invalidPayload('The number of pages must be a whole number between 1 and 500.');
        }

        $pocketsPerPage = filter_var($payload['pocketsPerPage'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 24]]);
        if (false === $pocketsPerPage) {
            throw CouldNotDeserializeCommand::invalidPayload('Pick the number of pockets per page.');
        }

        return new self(
            language: $language,
            cardSetId: $cardSetId,
            pageCount: $pageCount,
            pocketsPerPage: $pocketsPerPage,
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
}
