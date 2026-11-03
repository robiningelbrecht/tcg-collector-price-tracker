<?php

declare(strict_types=1);

namespace App\Domain\Sealed;

interface OwnedSealedProductRepository
{
    public function save(OwnedSealedProduct $ownedSealedProduct): void;

    public function delete(OwnedSealedProduct $ownedSealedProduct): void;

    public function find(int $productId): ?OwnedSealedProduct;
}
