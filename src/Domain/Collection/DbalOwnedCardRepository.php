<?php

declare(strict_types=1);

namespace App\Domain\Collection;

use App\Domain\TcgCollector\TcgRegion;
use App\Infrastructure\Repository\DbalRepository;

final readonly class DbalOwnedCardRepository extends DbalRepository implements OwnedCardRepository
{
    public function upsert(OwnedCard $ownedCard): void
    {
        $sql = 'INSERT INTO OwnedCard (tcgCollectorCardId, variantTypeId, languageId, name, setName, cardNumber, rarity, variantName, region, count, tcgCollectorSlug, importedOn)
        VALUES (:tcgCollectorCardId, :variantTypeId, :languageId, :name, :setName, :cardNumber, :rarity, :variantName, :region, :count, :tcgCollectorSlug, :importedOn)
        ON CONFLICT (tcgCollectorCardId, variantTypeId, languageId) DO UPDATE SET
            name = excluded.name,
            setName = excluded.setName,
            cardNumber = excluded.cardNumber,
            rarity = excluded.rarity,
            variantName = excluded.variantName,
            region = excluded.region,
            count = excluded.count,
            tcgCollectorSlug = excluded.tcgCollectorSlug,
            importedOn = excluded.importedOn';

        $this->connection->executeStatement($sql, [
            'tcgCollectorCardId' => $ownedCard->getTcgCollectorCardId(),
            'variantTypeId' => $ownedCard->getVariantTypeId(),
            'languageId' => $ownedCard->getLanguageId(),
            'name' => $ownedCard->getName(),
            'setName' => $ownedCard->getSetName(),
            'cardNumber' => $ownedCard->getCardNumber(),
            'rarity' => $ownedCard->getRarity(),
            'variantName' => $ownedCard->getVariantName(),
            'region' => $ownedCard->getRegion()->value,
            'count' => $ownedCard->getCount(),
            'tcgCollectorSlug' => $ownedCard->getTcgCollectorSlug(),
            'importedOn' => $ownedCard->getImportedOn()->format('Y-m-d H:i:s'),
        ]);
    }

    public function findAll(): OwnedCards
    {
        $results = $this->connection->executeQuery(
            'SELECT * FROM OwnedCard ORDER BY setName, cardNumber, variantTypeId'
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
        return OwnedCard::fromState(
            tcgCollectorCardId: (int) $result['tcgCollectorCardId'],
            variantTypeId: (int) $result['variantTypeId'],
            languageId: (int) $result['languageId'],
            name: (string) $result['name'],
            setName: (string) $result['setName'],
            cardNumber: (string) $result['cardNumber'],
            rarity: isset($result['rarity']) ? (string) $result['rarity'] : null,
            variantName: (string) $result['variantName'],
            region: TcgRegion::from((int) $result['region']),
            count: (int) $result['count'],
            tcgCollectorSlug: (string) $result['tcgCollectorSlug'],
            importedOn: new \DateTimeImmutable((string) $result['importedOn']),
        );
    }
}
