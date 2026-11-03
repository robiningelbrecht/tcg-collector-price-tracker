<?php

declare(strict_types=1);

namespace App\Domain\Sealed\Price;

use App\Infrastructure\Repository\DbalRepository;

final readonly class DbalSealedProductPriceRepository extends DbalRepository implements SealedProductPriceRepository
{
    public function upsert(SealedProductPrice $sealedProductPrice): void
    {
        $sql = 'INSERT INTO SealedProductPrice (productId, date, currency, low, avg, trend, avg1, avg7, avg30, sourceUpdatedOn, data)
        VALUES (:productId, :date, :currency, :low, :avg, :trend, :avg1, :avg7, :avg30, :sourceUpdatedOn, :data)
        ON CONFLICT (productId, date) DO UPDATE SET
            currency = excluded.currency,
            low = excluded.low,
            avg = excluded.avg,
            trend = excluded.trend,
            avg1 = excluded.avg1,
            avg7 = excluded.avg7,
            avg30 = excluded.avg30,
            sourceUpdatedOn = excluded.sourceUpdatedOn,
            data = excluded.data';

        $this->connection->executeStatement($sql, [
            'productId' => $sealedProductPrice->getProductId(),
            'date' => $sealedProductPrice->getDate()->format('Y-m-d'),
            'currency' => $sealedProductPrice->getCurrency(),
            'low' => $sealedProductPrice->getLow(),
            'avg' => $sealedProductPrice->getAvg(),
            'trend' => $sealedProductPrice->getTrend(),
            'avg1' => $sealedProductPrice->getAvg1(),
            'avg7' => $sealedProductPrice->getAvg7(),
            'avg30' => $sealedProductPrice->getAvg30(),
            'sourceUpdatedOn' => $sealedProductPrice->getSourceUpdatedOn()?->format('Y-m-d H:i:s'),
            'data' => json_encode($sealedProductPrice->getData(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}
