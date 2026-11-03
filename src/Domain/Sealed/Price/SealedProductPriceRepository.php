<?php

declare(strict_types=1);

namespace App\Domain\Sealed\Price;

interface SealedProductPriceRepository
{
    public function upsert(SealedProductPrice $sealedProductPrice): void;
}
