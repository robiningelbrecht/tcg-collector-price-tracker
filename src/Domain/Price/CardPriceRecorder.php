<?php

declare(strict_types=1);

namespace App\Domain\Price;

use App\Domain\TcgDex\TcgDexLanguage;

final readonly class CardPriceRecorder
{
    public function __construct(
        private CardPriceExtractor $cardPriceExtractor,
        private CardPriceRepository $cardPriceRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $card
     */
    public function record(TcgDexLanguage $language, string $cardId, array $card, \DateTimeImmutable $date): CardPrices
    {
        $cardPrices = $this->cardPriceExtractor->extract($language, $cardId, $card, $date);
        if ($cardPrices->isEmpty()) {
            return $cardPrices;
        }

        $this->cardPriceRepository->deleteForCardOnDate($language, $cardId, $date);
        foreach ($cardPrices as $cardPrice) {
            $this->cardPriceRepository->upsert($cardPrice);
        }

        return $cardPrices;
    }
}
