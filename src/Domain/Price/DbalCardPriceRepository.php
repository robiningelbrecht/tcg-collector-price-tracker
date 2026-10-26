<?php

declare(strict_types=1);

namespace App\Domain\Price;

use App\Domain\TcgDex\TcgDexLanguage;
use App\Infrastructure\Repository\DbalRepository;

final readonly class DbalCardPriceRepository extends DbalRepository implements CardPriceRepository
{
    public function upsert(CardPrice $cardPrice): void
    {
        $sql = 'INSERT INTO CardPrice (language, cardId, variantId, source, date, variantType, variantSubtype, variantSize, variantStamps, currency, low, mid, high, market, avg, trend, avg1, avg7, avg30, sourceUpdatedOn, data)
        VALUES (:language, :cardId, :variantId, :source, :date, :variantType, :variantSubtype, :variantSize, :variantStamps, :currency, :low, :mid, :high, :market, :avg, :trend, :avg1, :avg7, :avg30, :sourceUpdatedOn, :data)
        ON CONFLICT (language, cardId, variantId, source, date) DO UPDATE SET
            variantType = excluded.variantType,
            variantSubtype = excluded.variantSubtype,
            variantSize = excluded.variantSize,
            variantStamps = excluded.variantStamps,
            currency = excluded.currency,
            low = excluded.low,
            mid = excluded.mid,
            high = excluded.high,
            market = excluded.market,
            avg = excluded.avg,
            trend = excluded.trend,
            avg1 = excluded.avg1,
            avg7 = excluded.avg7,
            avg30 = excluded.avg30,
            sourceUpdatedOn = excluded.sourceUpdatedOn,
            data = excluded.data';

        $this->connection->executeStatement($sql, [
            'language' => $cardPrice->getLanguage()->value,
            'cardId' => $cardPrice->getCardId(),
            'variantId' => $cardPrice->getVariantId(),
            'source' => $cardPrice->getSource()->value,
            'date' => $cardPrice->getDate()->format('Y-m-d'),
            'variantType' => $cardPrice->getVariantType(),
            'variantSubtype' => $cardPrice->getVariantSubtype(),
            'variantSize' => $cardPrice->getVariantSize(),
            'variantStamps' => $cardPrice->getVariantStamps(),
            'currency' => $cardPrice->getCurrency(),
            'low' => $cardPrice->getLow(),
            'mid' => $cardPrice->getMid(),
            'high' => $cardPrice->getHigh(),
            'market' => $cardPrice->getMarket(),
            'avg' => $cardPrice->getAvg(),
            'trend' => $cardPrice->getTrend(),
            'avg1' => $cardPrice->getAvg1(),
            'avg7' => $cardPrice->getAvg7(),
            'avg30' => $cardPrice->getAvg30(),
            'sourceUpdatedOn' => $cardPrice->getSourceUpdatedOn()?->format('Y-m-d H:i:s'),
            'data' => json_encode($cardPrice->getData(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function deleteForCardOnDate(TcgDexLanguage $language, string $cardId, \DateTimeImmutable $date): void
    {
        $this->connection->executeStatement(
            'DELETE FROM CardPrice WHERE language = :language AND cardId = :cardId AND date = :date',
            ['language' => $language->value, 'cardId' => $cardId, 'date' => $date->format('Y-m-d')]
        );
    }
}
