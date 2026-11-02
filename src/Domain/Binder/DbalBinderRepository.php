<?php

declare(strict_types=1);

namespace App\Domain\Binder;

use App\Domain\TcgDex\TcgDexLanguage;
use App\Infrastructure\Repository\DbalRepository;

final readonly class DbalBinderRepository extends DbalRepository implements BinderRepository
{
    public function save(Binder $binder): void
    {
        $sql = 'INSERT INTO Binder (language, cardSetId, pageCount, pocketsPerPage, createdOn)
        VALUES (:language, :cardSetId, :pageCount, :pocketsPerPage, :createdOn)
        ON CONFLICT (language, cardSetId) DO UPDATE SET
            pageCount = excluded.pageCount,
            pocketsPerPage = excluded.pocketsPerPage';

        $this->connection->executeStatement($sql, [
            'language' => $binder->getLanguage()->value,
            'cardSetId' => $binder->getCardSetId(),
            'pageCount' => $binder->getPageCount(),
            'pocketsPerPage' => $binder->getPocketsPerPage(),
            'createdOn' => $binder->getCreatedOn()->format('Y-m-d H:i:s'),
        ]);
    }

    public function delete(Binder $binder): void
    {
        $this->connection->executeStatement(
            'DELETE FROM Binder WHERE language = :language AND cardSetId = :cardSetId',
            ['language' => $binder->getLanguage()->value, 'cardSetId' => $binder->getCardSetId()]
        );
    }

    public function find(TcgDexLanguage $language, string $cardSetId): ?Binder
    {
        $result = $this->connection->executeQuery(
            'SELECT * FROM Binder WHERE language = :language AND cardSetId = :cardSetId',
            ['language' => $language->value, 'cardSetId' => $cardSetId]
        )->fetchAssociative();

        return false === $result ? null : $this->hydrate($result);
    }

    public function findAll(): Binders
    {
        $results = $this->connection->executeQuery(
            'SELECT * FROM Binder ORDER BY createdOn, language, cardSetId'
        )->fetchAllAssociative();

        return Binders::fromArray(array_map(
            $this->hydrate(...),
            $results
        ));
    }

    /**
     * @param array<string, mixed> $result
     */
    private function hydrate(array $result): Binder
    {
        return Binder::fromState(
            language: TcgDexLanguage::from((string) $result['language']),
            cardSetId: (string) $result['cardSetId'],
            pageCount: (int) $result['pageCount'],
            pocketsPerPage: (int) $result['pocketsPerPage'],
            createdOn: new \DateTimeImmutable((string) $result['createdOn']),
        );
    }
}
