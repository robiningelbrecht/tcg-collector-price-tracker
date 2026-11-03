<?php

declare(strict_types=1);

namespace App\Domain\Sealed;

use App\Infrastructure\Repository\DbalRepository;

final readonly class DbalOwnedSealedProductRepository extends DbalRepository implements OwnedSealedProductRepository
{
    public function save(OwnedSealedProduct $ownedSealedProduct): void
    {
        $sql = 'INSERT INTO OwnedSealedProduct (productId, quantity, paidPrice, addedOn)
        VALUES (:productId, :quantity, :paidPrice, :addedOn)
        ON CONFLICT (productId) DO UPDATE SET
            quantity = excluded.quantity,
            paidPrice = excluded.paidPrice';

        $this->connection->executeStatement($sql, [
            'productId' => $ownedSealedProduct->getProductId(),
            'quantity' => $ownedSealedProduct->getQuantity(),
            'paidPrice' => $ownedSealedProduct->getPaidPrice(),
            'addedOn' => $ownedSealedProduct->getAddedOn()->format('Y-m-d H:i:s'),
        ]);
    }

    public function delete(OwnedSealedProduct $ownedSealedProduct): void
    {
        $this->connection->executeStatement(
            'DELETE FROM OwnedSealedProduct WHERE productId = :productId',
            ['productId' => $ownedSealedProduct->getProductId()]
        );
    }

    public function find(int $productId): ?OwnedSealedProduct
    {
        $result = $this->connection->executeQuery(
            'SELECT * FROM OwnedSealedProduct WHERE productId = :productId',
            ['productId' => $productId]
        )->fetchAssociative();

        return false === $result ? null : $this->hydrate($result);
    }

    /**
     * @param array<string, mixed> $result
     */
    private function hydrate(array $result): OwnedSealedProduct
    {
        return OwnedSealedProduct::fromState(
            productId: (int) $result['productId'],
            quantity: (int) $result['quantity'],
            paidPrice: isset($result['paidPrice']) ? (float) $result['paidPrice'] : null,
            addedOn: new \DateTimeImmutable((string) $result['addedOn']),
        );
    }
}
