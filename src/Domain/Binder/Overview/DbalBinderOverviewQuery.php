<?php

declare(strict_types=1);

namespace App\Domain\Binder\Overview;

use App\Domain\TcgDex\TcgDexLanguage;
use App\Infrastructure\Repository\DbalRepository;

final readonly class DbalBinderOverviewQuery extends DbalRepository implements BinderOverviewQuery
{
    public function find(): BinderOverviews
    {
        $rows = $this->connection->executeQuery(
            'SELECT Binder.language, Binder.cardSetId, Binder.pageCount, Binder.pocketsPerPage,
                CardSet.name AS setName, CardSet.serieName, CardSet.logo,
                COUNT(BinderPage.pageNumber) AS photographedPageCount,
                MAX(BinderPage.takenOn) AS lastTakenOn,
                MIN(BinderPage.takenOn) AS oldestTakenOn
            FROM Binder
            LEFT JOIN CardSet ON CardSet.language = Binder.language AND CardSet.cardSetId = Binder.cardSetId
            LEFT JOIN BinderPage ON BinderPage.language = Binder.language AND BinderPage.cardSetId = Binder.cardSetId AND BinderPage.pageNumber <= Binder.pageCount
            GROUP BY Binder.language, Binder.cardSetId
            ORDER BY CardSet.releaseDate, Binder.cardSetId'
        )->fetchAllAssociative();

        return BinderOverviews::fromArray(array_map(
            fn (array $row): BinderOverview => BinderOverview::fromState(
                language: TcgDexLanguage::from((string) $row['language']),
                cardSetId: (string) $row['cardSetId'],
                setName: isset($row['setName']) ? (string) $row['setName'] : (string) $row['cardSetId'],
                serieName: isset($row['serieName']) ? (string) $row['serieName'] : null,
                logo: isset($row['logo']) ? (string) $row['logo'] : null,
                pageCount: (int) $row['pageCount'],
                pocketsPerPage: (int) $row['pocketsPerPage'],
                photographedPageCount: (int) $row['photographedPageCount'],
                lastTakenOn: isset($row['lastTakenOn']) ? new \DateTimeImmutable((string) $row['lastTakenOn']) : null,
                oldestTakenOn: isset($row['oldestTakenOn']) ? new \DateTimeImmutable((string) $row['oldestTakenOn']) : null,
            ),
            $rows
        ));
    }

    public function findOwnedSetsWithoutBinder(): array
    {
        $rows = $this->connection->executeQuery(
            'SELECT DISTINCT CardSet.language, CardSet.cardSetId, CardSet.name, CardSet.serieId, CardSet.serieName, CardSet.releaseDate
            FROM OwnedCard
            JOIN CardSet ON CardSet.language = OwnedCard.language AND CardSet.cardSetId = OwnedCard.cardSetId
            LEFT JOIN Binder ON Binder.language = CardSet.language AND Binder.cardSetId = CardSet.cardSetId
            WHERE Binder.cardSetId IS NULL
            ORDER BY COALESCE(CardSet.releaseDate, \'9999-12-31\'), CardSet.name COLLATE NOCASE'
        )->fetchAllAssociative();

        $series = [];
        foreach ($rows as $row) {
            $serieId = isset($row['serieId']) ? (string) $row['serieId'] : 'unknown';
            $series[$serieId]['label'] ??= isset($row['serieName']) ? (string) $row['serieName'] : 'Other';
            $series[$serieId]['sets'][] = [
                'id' => $row['language'].'/'.$row['cardSetId'],
                'label' => (string) $row['name'],
            ];
        }

        return array_values($series);
    }
}
