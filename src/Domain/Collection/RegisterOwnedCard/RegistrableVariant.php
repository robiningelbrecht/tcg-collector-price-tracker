<?php

declare(strict_types=1);

namespace App\Domain\Collection\RegisterOwnedCard;

use App\Domain\Collection\OwnedCard;
use App\Domain\Collection\OwnedCards;
use App\Domain\Collection\VariantLabel;
use App\Domain\Price\CardPrice;
use App\Domain\Price\CardPrices;
use App\Domain\Price\PriceSource;

final readonly class RegistrableVariant
{
    private function __construct(
        private string $variantId,
        private string $label,
        private bool $tracked,
        private ?float $cardmarketTrend,
    ) {
    }

    /**
     * @param array<string, mixed> $card
     *
     * @return list<self>
     */
    public static function listFromTcgDex(array $card, OwnedCards $ownedCards, CardPrices $cardPrices): array
    {
        $trackedVariantIds = array_flip($ownedCards->map(fn (OwnedCard $ownedCard): string => $ownedCard->getPriceVariantId()));

        $trends = [];
        /** @var CardPrice $cardPrice */
        foreach ($cardPrices as $cardPrice) {
            if (PriceSource::CARDMARKET === $cardPrice->getSource() && null !== $cardPrice->getTrend()) {
                $trends[$cardPrice->getVariantId()] = $cardPrice->getTrend();
            }
        }

        $variants = [];
        foreach (is_array($card['variants_detailed'] ?? null) ? $card['variants_detailed'] : [] as $variant) {
            if (!is_array($variant) || !is_string($variant['variantId'] ?? null)) {
                continue;
            }

            $variantId = $variant['variantId'];
            $variants[] = new self(
                variantId: $variantId,
                label: VariantLabel::from(
                    is_string($variant['type'] ?? null) ? $variant['type'] : null,
                    is_string($variant['subtype'] ?? null) ? $variant['subtype'] : null,
                    is_string($variant['size'] ?? null) ? $variant['size'] : null,
                    is_array($variant['stamp'] ?? null) ? implode(',', array_filter($variant['stamp'], is_string(...))) : null,
                ),
                tracked: isset($trackedVariantIds[$variantId]),
                cardmarketTrend: $trends[$variantId] ?? null,
            );
        }

        return $variants;
    }

    public function getVariantId(): string
    {
        return $this->variantId;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isTracked(): bool
    {
        return $this->tracked;
    }

    public function getCardmarketTrend(): ?float
    {
        return $this->cardmarketTrend;
    }
}
