<?php

declare(strict_types=1);

namespace App\Domain\Collection;

use App\Domain\TcgDex\TcgDexLanguage;
use App\Infrastructure\Repository\DbalRepository;

final readonly class DbalOwnedCardRepository extends DbalRepository implements OwnedCardRepository
{
    public function upsert(OwnedCard $ownedCard): void
    {
        $sql = 'INSERT INTO OwnedCard (tcgCollectorCardId, variantTypeId, languageId, variantName, count, language, cardId, cardSetId, localId, name, category, rarity, illustrator, image, hp, data, importedOn)
        VALUES (:tcgCollectorCardId, :variantTypeId, :languageId, :variantName, :count, :language, :cardId, :cardSetId, :localId, :name, :category, :rarity, :illustrator, :image, :hp, :data, :importedOn)
        ON CONFLICT (tcgCollectorCardId, variantTypeId, languageId) DO UPDATE SET
            count = excluded.count,
            importedOn = excluded.importedOn';

        $this->connection->executeStatement($sql, [
            'tcgCollectorCardId' => $ownedCard->getTcgCollectorCardId(),
            'variantTypeId' => $ownedCard->getVariantTypeId(),
            'languageId' => $ownedCard->getLanguageId(),
            'variantName' => $ownedCard->getVariantName(),
            'count' => $ownedCard->getCount(),
            'language' => $ownedCard->getLanguage()->value,
            'cardId' => $ownedCard->getCardId(),
            'cardSetId' => $ownedCard->getCardSetId(),
            'localId' => $ownedCard->getLocalId(),
            'name' => $ownedCard->getName(),
            'category' => $ownedCard->getCategory(),
            'rarity' => $ownedCard->getRarity(),
            'illustrator' => $ownedCard->getIllustrator(),
            'image' => $ownedCard->getImage(),
            'hp' => $ownedCard->getHp(),
            'data' => json_encode($ownedCard->getData(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'importedOn' => $ownedCard->getImportedOn()->format('Y-m-d H:i:s'),
        ]);
    }

    public function find(int $tcgCollectorCardId, int $variantTypeId, int $languageId): ?OwnedCard
    {
        $result = $this->connection->executeQuery(
            'SELECT * FROM OwnedCard WHERE tcgCollectorCardId = :tcgCollectorCardId AND variantTypeId = :variantTypeId AND languageId = :languageId',
            [
                'tcgCollectorCardId' => $tcgCollectorCardId,
                'variantTypeId' => $variantTypeId,
                'languageId' => $languageId,
            ]
        )->fetchAssociative();

        return false === $result ? null : $this->hydrate($result);
    }

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
            'SELECT * FROM OwnedCard ORDER BY language, cardSetId, localId, variantTypeId'
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
            tcgCollectorCardId: (int) $result['tcgCollectorCardId'],
            variantTypeId: (int) $result['variantTypeId'],
            languageId: (int) $result['languageId'],
            variantName: (string) $result['variantName'],
            count: (int) $result['count'],
            language: TcgDexLanguage::from((string) $result['language']),
            cardId: (string) $result['cardId'],
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
