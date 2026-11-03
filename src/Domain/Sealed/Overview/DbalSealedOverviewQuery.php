<?php

declare(strict_types=1);

namespace App\Domain\Sealed\Overview;

use App\Infrastructure\Repository\DbalRepository;

final readonly class DbalSealedOverviewQuery extends DbalRepository implements SealedOverviewQuery
{
    public function find(): SealedOverviewProducts
    {
        $results = $this->connection->executeQuery(
            'SELECT OwnedSealedProduct.productId, OwnedSealedProduct.quantity, OwnedSealedProduct.paidPrice,
                SealedProduct.name, SealedProduct.categoryName,
                SealedProductPrice.date, SealedProductPrice.trend, SealedProductPrice.low
            FROM OwnedSealedProduct
            LEFT JOIN SealedProduct ON SealedProduct.productId = OwnedSealedProduct.productId
            LEFT JOIN SealedProductPrice ON SealedProductPrice.productId = OwnedSealedProduct.productId
                AND SealedProductPrice.date = (SELECT MAX(date) FROM SealedProductPrice WHERE productId = OwnedSealedProduct.productId)
            ORDER BY SealedProduct.name COLLATE NOCASE, OwnedSealedProduct.productId'
        )->fetchAllAssociative();

        return SealedOverviewProducts::fromArray(array_map(
            fn (array $result): SealedOverviewProduct => SealedOverviewProduct::create(
                productId: (int) $result['productId'],
                name: isset($result['name']) ? (string) $result['name'] : sprintf('Product %d', $result['productId']),
                categoryName: isset($result['categoryName']) ? (string) $result['categoryName'] : null,
                quantity: (int) $result['quantity'],
                paidPrice: isset($result['paidPrice']) ? (float) $result['paidPrice'] : null,
                trend: isset($result['trend']) ? (float) $result['trend'] : null,
                low: isset($result['low']) ? (float) $result['low'] : null,
                priceDate: isset($result['date']) ? new \DateTimeImmutable((string) $result['date']) : null,
            ),
            $results
        ));
    }
}
