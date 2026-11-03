<?php

declare(strict_types=1);

namespace App\Domain\Sealed;

use App\Infrastructure\Repository\DbalRepository;

final readonly class DbalSealedProductRepository extends DbalRepository implements SealedProductRepository
{
    public function upsert(SealedProduct $sealedProduct): void
    {
        $sql = 'INSERT INTO SealedProduct (productId, name, categoryName, expansionId, addedOn, data)
        VALUES (:productId, :name, :categoryName, :expansionId, :addedOn, :data)
        ON CONFLICT (productId) DO UPDATE SET
            name = excluded.name,
            categoryName = excluded.categoryName,
            expansionId = excluded.expansionId,
            addedOn = excluded.addedOn,
            data = excluded.data';

        $this->connection->executeStatement($sql, [
            'productId' => $sealedProduct->getProductId(),
            'name' => $sealedProduct->getName(),
            'categoryName' => $sealedProduct->getCategoryName(),
            'expansionId' => $sealedProduct->getExpansionId(),
            'addedOn' => $sealedProduct->getAddedOn()?->format('Y-m-d H:i:s'),
            'data' => json_encode($sealedProduct->getData(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function find(int $productId): ?SealedProduct
    {
        $result = $this->connection->executeQuery(
            'SELECT * FROM SealedProduct WHERE productId = :productId',
            ['productId' => $productId]
        )->fetchAssociative();

        return false === $result ? null : $this->hydrate($result);
    }

    public function search(string $query, int $limit): SealedProducts
    {
        $words = array_values(array_filter(preg_split('/\s+/', trim($query)) ?: [], fn (string $word): bool => '' !== $word));
        if ([] === $words) {
            return SealedProducts::empty();
        }

        $conditions = [];
        $parameters = ['limit' => $limit];
        foreach ($words as $index => $word) {
            $conditions[] = sprintf("name LIKE :word%d ESCAPE '\\'", $index);
            $parameters['word'.$index] = '%'.addcslashes($word, '%_\\').'%';
        }

        $results = $this->connection->executeQuery(
            sprintf('SELECT * FROM SealedProduct WHERE %s ORDER BY addedOn DESC, productId DESC LIMIT :limit', implode(' AND ', $conditions)),
            $parameters
        )->fetchAllAssociative();

        return SealedProducts::fromArray(array_map(
            $this->hydrate(...),
            $results
        ));
    }

    /**
     * @param array<string, mixed> $result
     */
    private function hydrate(array $result): SealedProduct
    {
        /** @var array<string, mixed> $data */
        $data = json_decode((string) $result['data'], true, 512, JSON_THROW_ON_ERROR);

        return SealedProduct::fromState(
            productId: (int) $result['productId'],
            name: (string) $result['name'],
            categoryName: isset($result['categoryName']) ? (string) $result['categoryName'] : null,
            expansionId: isset($result['expansionId']) ? (int) $result['expansionId'] : null,
            addedOn: isset($result['addedOn']) ? new \DateTimeImmutable((string) $result['addedOn']) : null,
            data: $data,
        );
    }
}
