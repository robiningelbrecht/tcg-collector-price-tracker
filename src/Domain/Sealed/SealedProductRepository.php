<?php

declare(strict_types=1);

namespace App\Domain\Sealed;

interface SealedProductRepository
{
    public function upsert(SealedProduct $sealedProduct): void;

    public function find(int $productId): ?SealedProduct;
}
