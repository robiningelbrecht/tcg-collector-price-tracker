<?php

declare(strict_types=1);

namespace App\Domain\Binder\Page\RemoveBinderPagePhoto;

use App\Domain\TcgDex\TcgDexLanguage;
use App\Infrastructure\CQRS\Command\Deserialize\CouldNotDeserializeCommand;
use App\Infrastructure\CQRS\Command\Deserialize\DeserializableCommand;
use App\Infrastructure\CQRS\Command\Deserialize\ProvidesCommandName;
use App\Infrastructure\CQRS\Command\DomainCommand;

final readonly class RemoveBinderPagePhoto extends DomainCommand implements DeserializableCommand
{
    use ProvidesCommandName;

    private function __construct(
        private TcgDexLanguage $language,
        private string $cardSetId,
        private int $pageNumber,
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

        return new self(
            language: $language,
            cardSetId: $cardSetId,
            pageNumber: $pageNumber,
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
}
