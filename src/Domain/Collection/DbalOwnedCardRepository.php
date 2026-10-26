<?php

declare(strict_types=1);

namespace App\Domain\Collection;

use App\Domain\TcgDex\TcgDexLanguage;
use App\Infrastructure\Repository\DbalRepository;

final readonly class DbalOwnedCardRepository extends DbalRepository implements OwnedCardRepository
{
    public function save(OwnedCard $ownedCard): void
    {
        $sql = 'INSERT INTO OwnedCard (cardId, priceVariantId, variantType, variantSubtype, variantSize, variantStamps, count, language, cardSetId, localId, name, category, rarity, illustrator, image, hp, data, importedOn)
        VALUES (:cardId, :priceVariantId, :variantType, :variantSubtype, :variantSize, :variantStamps, :count, :language, :cardSetId, :localId, :name, :category, :rarity, :illustrator, :image, :hp, :data, :importedOn)
        ON CONFLICT (cardId, priceVariantId) DO UPDATE SET
            variantType = excluded.variantType,
            variantSubtype = excluded.variantSubtype,
            variantSize = excluded.variantSize,
            variantStamps = excluded.variantStamps,
            count = excluded.count,
            language = excluded.language,
            cardSetId = excluded.cardSetId,
            localId = excluded.localId,
            name = excluded.name,
            category = excluded.category,
            rarity = excluded.rarity,
            illustrator = excluded.illustrator,
            image = excluded.image,
            hp = excluded.hp,
            data = excluded.data,
            importedOn = excluded.importedOn';

        $this->connection->executeStatement($sql, [
            'cardId' => $ownedCard->getCardId(),
            'priceVariantId' => $ownedCard->getPriceVariantId(),
            'variantType' => $ownedCard->getVariantType(),
            'variantSubtype' => $ownedCard->getVariantSubtype(),
            'variantSize' => $ownedCard->getVariantSize(),
            'variantStamps' => $ownedCard->getVariantStamps(),
            'count' => $ownedCard->getCount(),
            'language' => $ownedCard->getLanguage()->value,
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

    public function delete(OwnedCard $ownedCard): void
    {
        $this->connection->executeStatement(
            'DELETE FROM OwnedCard WHERE cardId = :cardId AND priceVariantId = :priceVariantId',
            ['cardId' => $ownedCard->getCardId(), 'priceVariantId' => $ownedCard->getPriceVariantId()]
        );
    }

    public function find(string $cardId, string $priceVariantId): ?OwnedCard
    {
        $result = $this->connection->executeQuery(
            'SELECT * FROM OwnedCard WHERE cardId = :cardId AND priceVariantId = :priceVariantId',
            ['cardId' => $cardId, 'priceVariantId' => $priceVariantId]
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
            variantType: isset($result['variantType']) ? (string) $result['variantType'] : null,
            variantSubtype: isset($result['variantSubtype']) ? (string) $result['variantSubtype'] : null,
            variantSize: isset($result['variantSize']) ? (string) $result['variantSize'] : null,
            variantStamps: isset($result['variantStamps']) ? (string) $result['variantStamps'] : null,
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
