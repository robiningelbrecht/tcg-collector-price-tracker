<?php

declare(strict_types=1);

namespace App\Domain\Collection;

use App\Domain\TcgDex\TcgDexLanguage;
use App\Infrastructure\Repository\DbalRepository;

final readonly class DbalOwnedCardRepository extends DbalRepository implements OwnedCardRepository
{
    public function findOneByCardId(TcgDexLanguage $language, string $cardId): ?OwnedCard
    {
        $result = $this->connection->executeQuery(
            'SELECT * FROM OwnedCard WHERE language = :language AND cardId = :cardId LIMIT 1',
            ['language' => $language->value, 'cardId' => $cardId]
        )->fetchAssociative();

        return false === $result ? null : $this->hydrate($result);
    }

    public function findAll(): OwnedCards
    {
        $results = $this->connection->executeQuery(
            'SELECT * FROM OwnedCard ORDER BY language, cardSetId, localId, priceVariantId'
        )->fetchAllAssociative();

        return OwnedCards::fromArray(array_map(
            $this->hydrate(...),
            $results
        ));
    }

    /**
     * @param array<string, mixed> $result
     */
    private function hydrate(array $result): OwnedCard
    {
        /** @var array<string, mixed> $data */
        $data = json_decode((string) $result['data'], true, 512, JSON_THROW_ON_ERROR);

        return OwnedCard::fromState(
            cardId: (string) $result['cardId'],
            priceVariantId: (string) $result['priceVariantId'],
            count: (int) $result['count'],
            language: TcgDexLanguage::from((string) $result['language']),
            cardSetId: (string) $result['cardSetId'],
            localId: (string) $result['localId'],
            name: (string) $result['name'],
            category: isset($result['category']) ? (string) $result['category'] : null,
            rarity: isset($result['rarity']) ? (string) $result['rarity'] : null,
            illustrator: isset($result['illustrator']) ? (string) $result['illustrator'] : null,
            image: isset($result['image']) ? (string) $result['image'] : null,
            hp: isset($result['hp']) ? (int) $result['hp'] : null,
            data: $data,
            importedOn: new \DateTimeImmutable((string) $result['importedOn']),
        );
    }
}
