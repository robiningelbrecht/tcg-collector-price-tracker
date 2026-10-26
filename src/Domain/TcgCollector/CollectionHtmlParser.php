<?php

declare(strict_types=1);

namespace App\Domain\TcgCollector;

use Symfony\Component\DomCrawler\Crawler;

final readonly class CollectionHtmlParser
{
    private const string APP_STATE_MARKER = 'appState: ';

    public function parse(string $html): CollectionCards
    {
        $appState = $this->decodeAppState($html);
        /** @var array<int, array{entries: list<array{cardVariantTypeId: int, cardLanguageId: int, cardCount: int}>}> $collectionCards */
        $collectionCards = $appState['cardIdToCardCollectionCardDtoMap'] ?? [];
        /** @var array<int, array{name: string}> $variantTypes */
        $variantTypes = $appState['idToCardVariantTypeDtoMap'] ?? [];
        /** @var array<int, int> $regions */
        $regions = $appState['cardIdToTcgRegionIdMap'] ?? [];

        $collection = CollectionCards::empty();
        $crawler = new Crawler($html);
        $crawler->filter('div.card-image-grid-item[data-card-id]')->each(function (Crawler $item) use ($collectionCards, $variantTypes, $regions, $collection): void {
            $cardId = (int) $item->attr('data-card-id');
            $entries = $collectionCards[$cardId]['entries'] ?? [];
            if ([] === $entries) {
                return;
            }

            $link = $item->filter('a.card-image-grid-item-link');
            $title = (string) $link->attr('title');
            $cardNumber = $this->text($item, '.card-image-grid-item-info-overlay-number');
            [$name, $setName] = $this->splitTitle($title, $cardNumber);
            if ('' === $setName) {
                $setName = (string) ($item->filter('img.set-symbol')->count() > 0
                    ? $item->filter('img.set-symbol')->attr('alt')
                    : $this->text($item, '.card-image-grid-item-info-overlay-expansion-code'));
            }
            $region = TcgRegion::tryFrom($regions[$cardId] ?? 1) ?? TcgRegion::INTERNATIONAL;

            $counts = [];
            foreach ($entries as $entry) {
                $key = $entry['cardVariantTypeId'].'-'.$entry['cardLanguageId'];
                $counts[$key] = ($counts[$key] ?? 0) + $entry['cardCount'];
            }

            foreach ($counts as $key => $count) {
                [$variantTypeId, $languageId] = array_map(intval(...), explode('-', $key));
                $collection->add(new CollectionCard(
                    tcgCollectorCardId: $cardId,
                    variantTypeId: $variantTypeId,
                    languageId: $languageId,
                    name: $name,
                    setName: $setName,
                    cardNumber: $cardNumber,
                    variantName: $variantTypes[$variantTypeId]['name'] ?? 'Unknown',
                    region: $region,
                    count: $count,
                ));
            }
        });

        return $collection;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeAppState(string $html): array
    {
        $start = strpos($html, self::APP_STATE_MARKER);
        if (false === $start) {
            throw new CouldNotParseCollectionHtml('Could not find window.tcgcollector appState');
        }
        $start += strlen(self::APP_STATE_MARKER);
        $end = strpos($html, "\n", $start);
        $json = rtrim(substr($html, $start, false === $end ? null : $end - $start), " \t\r,");

        try {
            $appState = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new CouldNotParseCollectionHtml('Could not decode appState: '.$e->getMessage(), $e->getCode(), previous: $e);
        }

        if (!is_array($appState)) {
            throw new CouldNotParseCollectionHtml('appState is not an object');
        }

        /* @var array<string, mixed> $appState */
        return $appState;
    }

    /**
     * @return array{string, string}
     */
    private function splitTitle(string $title, string $cardNumber): array
    {
        $openingParenthesis = strrpos($title, ' (');
        if (false === $openingParenthesis || !str_ends_with($title, ')')) {
            return [$title, ''];
        }

        $name = substr($title, 0, $openingParenthesis);
        $setAndNumber = substr($title, $openingParenthesis + 2, -1);
        $setName = str_ends_with($setAndNumber, ' '.$cardNumber)
            ? substr($setAndNumber, 0, -strlen(' '.$cardNumber))
            : '';

        return [$name, $setName];
    }

    private function text(Crawler $item, string $selector): string
    {
        $node = $item->filter($selector);

        return $node->count() > 0 ? trim($node->text()) : '';
    }
}
