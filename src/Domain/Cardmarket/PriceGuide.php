<?php

declare(strict_types=1);

namespace App\Domain\Cardmarket;

final readonly class PriceGuide
{
    /**
     * @param array<int, array<string, mixed>> $pricesByProductId
     */
    private function __construct(
        private ?\DateTimeImmutable $createdOn,
        private array $pricesByProductId,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromCardmarket(array $data): self
    {
        $pricesByProductId = [];
        foreach (is_array($data['priceGuides'] ?? null) ? $data['priceGuides'] : [] as $prices) {
            if (!is_array($prices) || !is_int($prices['idProduct'] ?? null)) {
                continue;
            }

            /** @var array<string, mixed> $productPrices */
            $productPrices = $prices;
            $pricesByProductId[$prices['idProduct']] = $productPrices;
        }

        $createdAt = $data['createdAt'] ?? null;

        return new self(
            createdOn: is_string($createdAt) && false !== strtotime($createdAt) ? new \DateTimeImmutable($createdAt) : null,
            pricesByProductId: $pricesByProductId,
        );
    }

    public function getCreatedOn(): ?\DateTimeImmutable
    {
        return $this->createdOn;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findPrices(int $productId): ?array
    {
        return $this->pricesByProductId[$productId] ?? null;
    }
}
