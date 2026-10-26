<?php

declare(strict_types=1);

namespace App\Domain\Set;

use App\Domain\TcgDex\TcgDexLanguage;
use App\Infrastructure\Repository\DbalRepository;

final readonly class DbalCardSetRepository extends DbalRepository implements CardSetRepository
{
    public function upsert(CardSet $cardSet): void
    {
        $sql = 'INSERT INTO CardSet (language, cardSetId, name, serieId, serieName, releaseDate, logo, symbol, cardCountOfficial, cardCountTotal, data, detailsImportedOn)
        VALUES (:language, :cardSetId, :name, :serieId, :serieName, :releaseDate, :logo, :symbol, :cardCountOfficial, :cardCountTotal, :data, :detailsImportedOn)
        ON CONFLICT (language, cardSetId) DO UPDATE SET
            name = excluded.name,
            serieId = COALESCE(excluded.serieId, CardSet.serieId),
            serieName = COALESCE(excluded.serieName, CardSet.serieName),
            releaseDate = COALESCE(excluded.releaseDate, CardSet.releaseDate),
            logo = COALESCE(excluded.logo, CardSet.logo),
            symbol = COALESCE(excluded.symbol, CardSet.symbol),
            cardCountOfficial = COALESCE(excluded.cardCountOfficial, CardSet.cardCountOfficial),
            cardCountTotal = COALESCE(excluded.cardCountTotal, CardSet.cardCountTotal),
            data = CASE WHEN excluded.detailsImportedOn IS NOT NULL OR CardSet.detailsImportedOn IS NULL THEN excluded.data ELSE CardSet.data END,
            detailsImportedOn = COALESCE(excluded.detailsImportedOn, CardSet.detailsImportedOn)';

        $this->connection->executeStatement($sql, [
            'language' => $cardSet->getLanguage()->value,
            'cardSetId' => $cardSet->getCardSetId(),
            'name' => $cardSet->getName(),
            'serieId' => $cardSet->getSerieId(),
            'serieName' => $cardSet->getSerieName(),
            'releaseDate' => $cardSet->getReleaseDate(),
            'logo' => $cardSet->getLogo(),
            'symbol' => $cardSet->getSymbol(),
            'cardCountOfficial' => $cardSet->getCardCountOfficial(),
            'cardCountTotal' => $cardSet->getCardCountTotal(),
            'data' => json_encode($cardSet->getData(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'detailsImportedOn' => $cardSet->getDetailsImportedOn()?->format('Y-m-d H:i:s'),
        ]);
    }

    public function find(TcgDexLanguage $language, string $cardSetId): ?CardSet
    {
        $result = $this->connection->executeQuery(
            'SELECT * FROM CardSet WHERE language = :language AND cardSetId = :cardSetId',
            ['language' => $language->value, 'cardSetId' => $cardSetId]
        )->fetchAssociative();

        return false === $result ? null : $this->hydrate($result);
    }

    public function findAll(TcgDexLanguage $language): CardSets
    {
        $results = $this->connection->executeQuery(
            'SELECT * FROM CardSet WHERE language = :language ORDER BY name COLLATE NOCASE, cardSetId',
            ['language' => $language->value]
        )->fetchAllAssociative();

        return CardSets::fromArray(array_map(
            $this->hydrate(...),
            $results
        ));
    }

    /**
     * @param array<string, mixed> $result
     */
    private function hydrate(array $result): CardSet
    {
        /** @var array<string, mixed> $data */
        $data = json_decode((string) $result['data'], true, 512, JSON_THROW_ON_ERROR);

        return CardSet::fromState(
            language: TcgDexLanguage::from((string) $result['language']),
            cardSetId: (string) $result['cardSetId'],
            name: (string) $result['name'],
            serieId: isset($result['serieId']) ? (string) $result['serieId'] : null,
            serieName: isset($result['serieName']) ? (string) $result['serieName'] : null,
            releaseDate: isset($result['releaseDate']) ? (string) $result['releaseDate'] : null,
            logo: isset($result['logo']) ? (string) $result['logo'] : null,
            symbol: isset($result['symbol']) ? (string) $result['symbol'] : null,
            cardCountOfficial: isset($result['cardCountOfficial']) ? (int) $result['cardCountOfficial'] : null,
            cardCountTotal: isset($result['cardCountTotal']) ? (int) $result['cardCountTotal'] : null,
            data: $data,
            detailsImportedOn: isset($result['detailsImportedOn']) ? new \DateTimeImmutable((string) $result['detailsImportedOn']) : null,
        );
    }
}
