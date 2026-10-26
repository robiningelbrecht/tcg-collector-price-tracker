<?php

declare(strict_types=1);

namespace App\Domain\Collection\Overview;

use App\Domain\Collection\VariantLabel;
use App\Domain\Price\PriceSource;
use App\Domain\TcgDex\TcgDexLanguage;
use App\Infrastructure\Repository\DbalRepository;

final readonly class DbalCollectionOverviewQuery extends DbalRepository implements CollectionOverviewQuery
{
    public function find(): OverviewCards
    {
        $pricesPerVariant = $this->findPricesPerVariant();
        $pricedCards = $this->findPricedCards();

        $rows = $this->connection->executeQuery(
            'SELECT OwnedCard.language, OwnedCard.cardId, OwnedCard.localId, OwnedCard.name, OwnedCard.rarity, OwnedCard.image,
                OwnedCard.cardSetId, OwnedCard.count, OwnedCard.priceVariantId,
                OwnedCard.variantType, OwnedCard.variantSubtype, OwnedCard.variantSize, OwnedCard.variantStamps,
                CardSet.name AS setName, CardSet.cardCountOfficial AS setCardCountOfficial, CardSet.releaseDate AS setReleaseDate,
                CardSet.serieId, CardSet.serieName
            FROM OwnedCard
            LEFT JOIN CardSet ON CardSet.language = OwnedCard.language AND CardSet.cardSetId = OwnedCard.cardSetId
            ORDER BY OwnedCard.language, CardSet.releaseDate, OwnedCard.cardSetId, OwnedCard.localId, OwnedCard.priceVariantId'
        )->fetchAllAssociative();

        /** @var array<string, list<array<string, mixed>>> $rowsPerCard */
        $rowsPerCard = [];
        foreach ($rows as $row) {
            $rowsPerCard[$row['language'].'|'.$row['cardId']][] = $row;
        }

        $cards = OverviewCards::empty();
        foreach ($rowsPerCard as $cardRows) {
            $first = $cardRows[0];
            $variants = OverviewVariants::empty();
            foreach ($cardRows as $row) {
                $cardKey = $row['language'].'|'.$row['cardId'];
                $priceVariantId = (string) $row['priceVariantId'];
                $prices = PricePoints::fromArray($pricesPerVariant[$cardKey.'|'.$priceVariantId] ?? []);

                $variants->add(OverviewVariant::fromState(
                    label: VariantLabel::from(
                        isset($row['variantType']) ? (string) $row['variantType'] : null,
                        isset($row['variantSubtype']) ? (string) $row['variantSubtype'] : null,
                        isset($row['variantSize']) ? (string) $row['variantSize'] : null,
                        isset($row['variantStamps']) ? (string) $row['variantStamps'] : null,
                    ),
                    count: (int) $row['count'],
                    priceVariantId: $priceVariantId,
                    prices: $prices,
                    missingPriceReason: match (true) {
                        !$prices->isEmpty() => null,
                        !isset($pricedCards[$cardKey]) => MissingPriceReason::CARD_NOT_PRICED,
                        default => MissingPriceReason::VARIANT_NOT_PRICED,
                    },
                ));
            }

            $cards->add(OverviewCard::fromState(
                language: TcgDexLanguage::from((string) $first['language']),
                cardId: (string) $first['cardId'],
                localId: (string) $first['localId'],
                name: (string) $first['name'],
                rarity: isset($first['rarity']) ? (string) $first['rarity'] : null,
                image: isset($first['image']) ? (string) $first['image'] : null,
                cardSetId: (string) $first['cardSetId'],
                setName: isset($first['setName']) ? (string) $first['setName'] : (string) $first['cardSetId'],
                setCardCountOfficial: isset($first['setCardCountOfficial']) ? (int) $first['setCardCountOfficial'] : null,
                setReleaseDate: isset($first['setReleaseDate']) ? (string) $first['setReleaseDate'] : null,
                serieId: isset($first['serieId']) ? (string) $first['serieId'] : null,
                serieName: isset($first['serieName']) ? (string) $first['serieName'] : null,
                variants: $variants,
            ));
        }

        return $cards;
    }

    /**
     * @return array<string, true>
     */
    private function findPricedCards(): array
    {
        $rows = $this->connection->executeQuery(
            'SELECT DISTINCT language, cardId FROM CardPrice WHERE source = :source AND trend IS NOT NULL',
            ['source' => PriceSource::CARDMARKET->value]
        )->fetchAllAssociative();

        $pricedCards = [];
        foreach ($rows as $row) {
            $pricedCards[$row['language'].'|'.$row['cardId']] = true;
        }

        return $pricedCards;
    }

    /**
     * @return array<string, list<PricePoint>>
     */
    private function findPricesPerVariant(): array
    {
        $rows = $this->connection->executeQuery(
            'SELECT CardPrice.language, CardPrice.cardId, CardPrice.variantId, CardPrice.date, CardPrice.trend, CardPrice.avg7, CardPrice.avg30
            FROM CardPrice
            WHERE CardPrice.source = :source
            AND CardPrice.trend IS NOT NULL
            AND EXISTS (
                SELECT 1 FROM OwnedCard
                WHERE OwnedCard.language = CardPrice.language
                AND OwnedCard.cardId = CardPrice.cardId
                AND OwnedCard.priceVariantId = CardPrice.variantId
            )
            ORDER BY CardPrice.date',
            ['source' => PriceSource::CARDMARKET->value]
        )->fetchAllAssociative();

        $pricesPerVariant = [];
        foreach ($rows as $row) {
            $pricesPerVariant[$row['language'].'|'.$row['cardId'].'|'.$row['variantId']][] = PricePoint::fromState(
                date: new \DateTimeImmutable((string) $row['date']),
                trend: (float) $row['trend'],
                avg7: isset($row['avg7']) ? (float) $row['avg7'] : null,
                avg30: isset($row['avg30']) ? (float) $row['avg30'] : null,
            );
        }

        return $pricesPerVariant;
    }
}
