<?php

declare(strict_types=1);

namespace App\Domain\Collection;

final readonly class PriceVariantResolver
{
    private const string STANDARD_SIZE = 'standard';
    private const string UNLIMITED_SUBTYPE = 'unlimited';

    /** @var array<string, array{type: ?string, stamp: ?string}> */
    private const array TCG_COLLECTOR_VARIANTS = [
        'Normal' => ['type' => 'normal', 'stamp' => null],
        'Non-holo' => ['type' => 'normal', 'stamp' => null],
        'Normal Holo' => ['type' => 'holo', 'stamp' => null],
        'Reverse Holo' => ['type' => 'reverse', 'stamp' => null],
        '1st Edition' => ['type' => 'normal', 'stamp' => '1st-edition'],
        '1st Edition Holo' => ['type' => 'holo', 'stamp' => '1st-edition'],
        'Expansion Stamp Holo' => ['type' => null, 'stamp' => 'set-logo'],
    ];

    public function __construct(
        private ManualPriceVariantMap $manualPriceVariantMap,
    ) {
    }

    public function resolve(OwnedCard $ownedCard): ?string
    {
        if (null !== $variantId = $this->manualPriceVariantMap->find($ownedCard->getTcgCollectorCardId(), $ownedCard->getVariantTypeId())) {
            return $variantId;
        }

        [$variantName, $subtype] = $this->splitVariantName($ownedCard->getVariantName());
        $wanted = self::TCG_COLLECTOR_VARIANTS[$variantName] ?? null;
        if (null === $wanted) {
            return null;
        }

        $variants = array_values(array_filter(
            $this->variants($ownedCard),
            fn (array $variant): bool => self::STANDARD_SIZE === ($variant['size'] ?? self::STANDARD_SIZE),
        ));

        $candidates = $this->candidates($variants, $wanted, $subtype);
        if ([] === $candidates && null === $subtype) {
            $candidates = $this->candidates($variants, $wanted, self::UNLIMITED_SUBTYPE);
        }

        if (1 === count($candidates)) {
            return $candidates[0]['variantId'];
        }

        if ([] === $candidates && 1 === count($variants)) {
            return $variants[0]['variantId'];
        }

        return null;
    }

    /**
     * @return list<array{variantId: string, type: ?string, subtype: ?string, size: ?string, stamps: list<string>}>
     */
    public function variants(OwnedCard $ownedCard): array
    {
        $data = $ownedCard->getData();
        $variants = [];
        foreach (is_array($data['variants_detailed'] ?? null) ? $data['variants_detailed'] : [] as $variant) {
            if (!is_array($variant) || !is_string($variant['variantId'] ?? null)) {
                continue;
            }

            $variants[] = [
                'variantId' => $variant['variantId'],
                'type' => is_string($variant['type'] ?? null) ? $variant['type'] : null,
                'subtype' => is_string($variant['subtype'] ?? null) ? $variant['subtype'] : null,
                'size' => is_string($variant['size'] ?? null) ? $variant['size'] : null,
                'stamps' => is_array($variant['stamp'] ?? null) ? array_values(array_filter($variant['stamp'], is_string(...))) : [],
            ];
        }

        return $variants;
    }

    /**
     * @param list<array{variantId: string, type: ?string, subtype: ?string, size: ?string, stamps: list<string>}> $variants
     * @param array{type: ?string, stamp: ?string}                                                                 $wanted
     *
     * @return list<array{variantId: string, type: ?string, subtype: ?string, size: ?string, stamps: list<string>}>
     */
    private function candidates(array $variants, array $wanted, ?string $subtype): array
    {
        return array_values(array_filter(
            $variants,
            fn (array $variant): bool => (null === $wanted['type'] || $wanted['type'] === $variant['type'])
                && $variant['stamps'] === (null === $wanted['stamp'] ? [] : [$wanted['stamp']])
                && $variant['subtype'] === $subtype,
        ));
    }

    /**
     * @return array{string, ?string}
     */
    private function splitVariantName(string $variantName): array
    {
        if (!preg_match('/^(.+?)\s*\((.+)\)$/', $variantName, $matches)) {
            return [$variantName, null];
        }

        return [$matches[1], trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($matches[2])), '-')];
    }
}
