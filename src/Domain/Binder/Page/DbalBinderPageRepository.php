<?php

declare(strict_types=1);

namespace App\Domain\Binder\Page;

use App\Domain\Binder\Binder;
use App\Domain\TcgDex\TcgDexLanguage;
use App\Infrastructure\Repository\DbalRepository;

final readonly class DbalBinderPageRepository extends DbalRepository implements BinderPageRepository
{
    public function save(BinderPage $page): void
    {
        $sql = 'INSERT INTO BinderPage (language, cardSetId, pageNumber, fileName, takenOn, uploadedOn)
        VALUES (:language, :cardSetId, :pageNumber, :fileName, :takenOn, :uploadedOn)
        ON CONFLICT (language, cardSetId, pageNumber) DO UPDATE SET
            fileName = excluded.fileName,
            takenOn = excluded.takenOn,
            uploadedOn = excluded.uploadedOn';

        $this->connection->executeStatement($sql, [
            'language' => $page->getLanguage()->value,
            'cardSetId' => $page->getCardSetId(),
            'pageNumber' => $page->getPageNumber(),
            'fileName' => $page->getFileName(),
            'takenOn' => $page->getTakenOn()->format('Y-m-d H:i:s'),
            'uploadedOn' => $page->getUploadedOn()->format('Y-m-d H:i:s'),
        ]);
    }

    public function delete(BinderPage $page): void
    {
        $this->connection->executeStatement(
            'DELETE FROM BinderPage WHERE language = :language AND cardSetId = :cardSetId AND pageNumber = :pageNumber',
            [
                'language' => $page->getLanguage()->value,
                'cardSetId' => $page->getCardSetId(),
                'pageNumber' => $page->getPageNumber(),
            ]
        );
    }

    public function find(Binder $binder, int $pageNumber): ?BinderPage
    {
        $result = $this->connection->executeQuery(
            'SELECT * FROM BinderPage WHERE language = :language AND cardSetId = :cardSetId AND pageNumber = :pageNumber',
            [
                'language' => $binder->getLanguage()->value,
                'cardSetId' => $binder->getCardSetId(),
                'pageNumber' => $pageNumber,
            ]
        )->fetchAssociative();

        return false === $result ? null : $this->hydrate($result);
    }

    public function findByBinder(Binder $binder): BinderPages
    {
        $results = $this->connection->executeQuery(
            'SELECT * FROM BinderPage WHERE language = :language AND cardSetId = :cardSetId ORDER BY pageNumber',
            ['language' => $binder->getLanguage()->value, 'cardSetId' => $binder->getCardSetId()]
        )->fetchAllAssociative();

        return BinderPages::fromArray(array_map(
            $this->hydrate(...),
            $results
        ));
    }

    /**
     * @param array<string, mixed> $result
     */
    private function hydrate(array $result): BinderPage
    {
        return BinderPage::fromState(
            language: TcgDexLanguage::from((string) $result['language']),
            cardSetId: (string) $result['cardSetId'],
            pageNumber: (int) $result['pageNumber'],
            fileName: (string) $result['fileName'],
            takenOn: new \DateTimeImmutable((string) $result['takenOn']),
            uploadedOn: new \DateTimeImmutable((string) $result['uploadedOn']),
        );
    }
}
