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
        $variants = array_values(array_filter(
            is_array($card['variants_detailed'] ?? null) ? $card['variants_detailed'] : [],
            fn (mixed $variant): bool => is_array($variant) && is_string($variant['variantId'] ?? null),
        ));
        $pricing = is_array($card['pricing'] ?? null) ? $card['pricing'] : [];
        $topCardmarket = is_array($pricing['cardmarket'] ?? null) ? $pricing['cardmarket'] : null;
        $plainVariantsWithoutCardmarket = $this->plainVariantsWithoutCardmarket($variants);
        $topCardmarketIsClaimed = null !== $topCardmarket && in_array(
            $topCardmarket['idProduct'] ?? null,
            array_map(fn (array $variant): mixed => is_array($variant['thirdParty'] ?? null) ? ($variant['thirdParty']['cardmarket'] ?? null) : null, $variants),
            true,
        );

        foreach ($variants as $variant) {
            $variantType = $this->stringOrNull($variant['type'] ?? null);
            $context = [
                'language' => $language,
                'cardId' => $cardId,
                'variantId' => (string) $variant['variantId'],
                'date' => $date,
                'variantType' => $variantType,
                'variantSubtype' => $this->stringOrNull($variant['subtype'] ?? null),
                'variantSize' => $this->stringOrNull($variant['size'] ?? null),
                'variantStamps' => is_array($variant['stamp'] ?? null) ? implode(',', array_filter($variant['stamp'], is_string(...))) : null,
            ];
            $variantPricing = is_array($variant['pricing'] ?? null) ? $variant['pricing'] : [];

            if (is_array($variantPricing['cardmarket'] ?? null)) {
                $this->addCardmarketPrice($cardPrices, $context, $variantPricing['cardmarket'], '');
            } elseif (null !== $topCardmarket && !$topCardmarketIsClaimed && in_array($variant, $plainVariantsWithoutCardmarket[$this->isReverse($variant) ? 'reverse' : 'other'], true)
                && 1 === count($plainVariantsWithoutCardmarket[$this->isReverse($variant) ? 'reverse' : 'other'])) {
                $this->addCardmarketPrice($cardPrices, $context, $topCardmarket, $this->isReverse($variant) ? '-holo' : '');
            }

            if (is_array($tcgplayer = $variantPricing['tcgplayer'] ?? null)) {
                $prices = null !== $variantType && is_array($tcgplayer[$variantType] ?? null) ? $tcgplayer[$variantType] : $tcgplayer;
                $this->addTcgplayerPrice($cardPrices, $context, $tcgplayer, $prices);
            }
        }

        if ([] !== $variants) {
            return $cardPrices;
        }

        if (null !== $topCardmarket) {
            foreach (['base' => [null, ''], 'reverse' => ['reverse', '-holo']] as $variantId => [$variantType, $suffix]) {
                $this->addCardmarketPrice($cardPrices, $this->fallbackContext($language, $cardId, $date, $variantId, $variantType), $topCardmarket, $suffix);
            }
        }

        if (is_array($tcgplayer = $pricing['tcgplayer'] ?? null)) {
            foreach ($tcgplayer as $variantType => $prices) {
                if (is_array($prices)) {
                    $this->addTcgplayerPrice($cardPrices, $this->fallbackContext($language, $cardId, $date, (string) $variantType, (string) $variantType), $tcgplayer, $prices);
                }
            }
        }

        return $cardPrices;
    }

    /**
     * @param list<array<mixed>> $variants
     *
     * @return array{reverse: list<array<mixed>>, other: list<array<mixed>>}
     */
    private function plainVariantsWithoutCardmarket(array $variants): array
    {
        $plain = ['reverse' => [], 'other' => []];
        foreach ($variants as $variant) {
            $hasCardmarket = is_array($variant['pricing'] ?? null) && is_array($variant['pricing']['cardmarket'] ?? null);
            if ($hasCardmarket || !empty($variant['stamp']) || null !== ($variant['subtype'] ?? null)) {
                continue;
            }
            $plain[$this->isReverse($variant) ? 'reverse' : 'other'][] = $variant;
        }

        return $plain;
    }

    /**
     * @param array<mixed> $variant
     */
    private function isReverse(array $variant): bool
    {
        return 'reverse' === ($variant['type'] ?? null);
    }

    /**
     * @return array{language: TcgDexLanguage, cardId: string, variantId: string, date: \DateTimeImmutable, variantType: ?string, variantSubtype: ?string, variantSize: ?string, variantStamps: ?string}
     */
    private function fallbackContext(TcgDexLanguage $language, string $cardId, \DateTimeImmutable $date, string $variantId, ?string $variantType): array
    {
        return [
            'language' => $language,
            'cardId' => $cardId,
            'variantId' => $variantId,
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
