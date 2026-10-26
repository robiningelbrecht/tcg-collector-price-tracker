<?php

declare(strict_types=1);

namespace App\Domain\Price;

use App\Domain\TcgDex\TcgDexLanguage;

final readonly class CardPriceExtractor
{
    /**
     * @param array<string, mixed> $card
     */
    public function extract(TcgDexLanguage $language, string $cardId, array $card, \DateTimeImmutable $date): CardPrices
    {
        $cardPrices = CardPrices::empty();
        $variants = is_array($card['variants_detailed'] ?? null) ? $card['variants_detailed'] : [];

        foreach ($variants as $variant) {
            if (!is_array($variant) || !is_string($variant['variantId'] ?? null) || !is_array($variant['pricing'] ?? null)) {
                continue;
            }

            $variantType = $this->stringOrNull($variant['type'] ?? null);
            $context = [
                'language' => $language,
                'cardId' => $cardId,
                'variantId' => $variant['variantId'],
                'date' => $date,
                'variantType' => $variantType,
                'variantSubtype' => $this->stringOrNull($variant['subtype'] ?? null),
                'variantSize' => $this->stringOrNull($variant['size'] ?? null),
                'variantStamps' => is_array($variant['stamp'] ?? null) ? implode(',', array_filter($variant['stamp'], is_string(...))) : null,
            ];

            if (is_array($variant['pricing']['cardmarket'] ?? null)) {
                $this->addCardmarketPrice($cardPrices, $context, $variant['pricing']['cardmarket'], '');
            }

            if (is_array($tcgplayer = $variant['pricing']['tcgplayer'] ?? null)) {
                $prices = null !== $variantType && is_array($tcgplayer[$variantType] ?? null) ? $tcgplayer[$variantType] : $tcgplayer;
                $this->addTcgplayerPrice($cardPrices, $context, $tcgplayer, $prices);
            }
        }

        if (!$cardPrices->isEmpty() || !is_array($card['pricing'] ?? null)) {
            return $cardPrices;
        }

        if (is_array($cardmarket = $card['pricing']['cardmarket'] ?? null)) {
            foreach (['normal' => '', 'holo' => '-holo'] as $variantType => $suffix) {
                $this->addCardmarketPrice($cardPrices, $this->fallbackContext($language, $cardId, $date, $variantType), $cardmarket, $suffix);
            }
        }

        if (is_array($tcgplayer = $card['pricing']['tcgplayer'] ?? null)) {
            foreach ($tcgplayer as $variantType => $prices) {
                if (is_array($prices)) {
                    $this->addTcgplayerPrice($cardPrices, $this->fallbackContext($language, $cardId, $date, (string) $variantType), $tcgplayer, $prices);
                }
            }
        }

        return $cardPrices;
    }

    /**
     * @return array{language: TcgDexLanguage, cardId: string, variantId: string, date: \DateTimeImmutable, variantType: ?string, variantSubtype: ?string, variantSize: ?string, variantStamps: ?string}
     */
    private function fallbackContext(TcgDexLanguage $language, string $cardId, \DateTimeImmutable $date, string $variantType): array
    {
        return [
            'language' => $language,
            'cardId' => $cardId,
            'variantId' => $variantType,
            'date' => $date,
            'variantType' => $variantType,
            'variantSubtype' => null,
            'variantSize' => null,
            'variantStamps' => null,
        ];
    }

    /**
     * @param array{language: TcgDexLanguage, cardId: string, variantId: string, date: \DateTimeImmutable, variantType: ?string, variantSubtype: ?string, variantSize: ?string, variantStamps: ?string} $context
     * @param array<mixed>                                                                                                                                                                              $block
     */
    private function addCardmarketPrice(CardPrices $cardPrices, array $context, array $block, string $suffix): void
    {
        $values = [];
        foreach (['low', 'avg', 'trend', 'avg1', 'avg7', 'avg30'] as $field) {
            $values[$field] = $this->floatOrNull($block[$field.$suffix] ?? null);
        }

        if ([] === array_filter($values, fn (?float $value): bool => null !== $value)) {
            return;
        }

        $cardPrices->add(CardPrice::create(
            language: $context['language'],
            cardId: $context['cardId'],
            variantId: $context['variantId'],
            source: PriceSource::CARDMARKET,
            date: $context['date'],
            variantType: $context['variantType'],
            variantSubtype: $context['variantSubtype'],
            variantSize: $context['variantSize'],
            variantStamps: $context['variantStamps'],
            currency: $this->stringOrNull($block['unit'] ?? null) ?? 'EUR',
            low: $values['low'],
            mid: null,
            high: null,
            market: null,
            avg: $values['avg'],
            trend: $values['trend'],
            avg1: $values['avg1'],
            avg7: $values['avg7'],
            avg30: $values['avg30'],
            sourceUpdatedOn: $this->dateOrNull($block['updated'] ?? null),
            data: $this->stringKeyed($block),
        ));
    }

    /**
     * @param array{language: TcgDexLanguage, cardId: string, variantId: string, date: \DateTimeImmutable, variantType: ?string, variantSubtype: ?string, variantSize: ?string, variantStamps: ?string} $context
     * @param array<mixed>                                                                                                                                                                              $block
     * @param array<mixed>                                                                                                                                                                              $prices
     */
    private function addTcgplayerPrice(CardPrices $cardPrices, array $context, array $block, array $prices): void
    {
        $values = [];
        foreach (['low' => 'lowPrice', 'mid' => 'midPrice', 'high' => 'highPrice', 'market' => 'marketPrice'] as $field => $key) {
            $values[$field] = $this->floatOrNull($prices[$key] ?? null);
        }

        if ([] === array_filter($values, fn (?float $value): bool => null !== $value)) {
            return;
        }

        $cardPrices->add(CardPrice::create(
            language: $context['language'],
            cardId: $context['cardId'],
            variantId: $context['variantId'],
            source: PriceSource::TCGPLAYER,
            date: $context['date'],
            variantType: $context['variantType'],
            variantSubtype: $context['variantSubtype'],
            variantSize: $context['variantSize'],
            variantStamps: $context['variantStamps'],
            currency: $this->stringOrNull($block['unit'] ?? null) ?? 'USD',
            low: $values['low'],
            mid: $values['mid'],
            high: $values['high'],
            market: $values['market'],
            avg: null,
            trend: null,
            avg1: null,
            avg7: null,
            avg30: null,
            sourceUpdatedOn: $this->dateOrNull($block['updated'] ?? null),
            data: $this->stringKeyed($prices),
        ));
    }

    private function floatOrNull(mixed $value): ?float
    {
        return is_int($value) || is_float($value) ? (float) $value : null;
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    private function dateOrNull(mixed $value): ?\DateTimeImmutable
    {
        if (!is_string($value)) {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @param array<mixed> $values
     *
     * @return array<string, mixed>
     */
    private function stringKeyed(array $values): array
    {
        $result = [];
        foreach ($values as $key => $value) {
            $result[(string) $key] = $value;
        }

        return $result;
    }
}
