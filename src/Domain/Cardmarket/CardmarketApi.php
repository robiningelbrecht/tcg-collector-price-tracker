<?php

declare(strict_types=1);

namespace App\Domain\Cardmarket;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

final readonly class CardmarketApi
{
    private const string BASE_URI = 'https://downloads.s3.cardmarket.com/productCatalog/';
    private const int POKEMON_GAME_ID = 6;

    public function __construct(
        private Client $client,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getSealedProducts(): array
    {
        $data = $this->get(sprintf('productList/products_nonsingles_%d.json', self::POKEMON_GAME_ID));
        if (!is_array($data['products'] ?? null)) {
            throw new CouldNotReachCardmarket('Cardmarket returned a product list without products');
        }

        /** @var list<array<string, mixed>> $products */
        $products = array_values(array_filter($data['products'], is_array(...)));

        return $products;
    }

    /**
     * @return array<mixed>
     */
    private function get(string $path): array
    {
        $response = $this->client->request('GET', self::BASE_URI.$path, [
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::TIMEOUT => 120,
        ]);

        if ($response->getStatusCode() >= 400) {
            throw new CouldNotReachCardmarket(sprintf('Cardmarket returned %d for %s', $response->getStatusCode(), $path));
        }

        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new CouldNotReachCardmarket(sprintf('Cardmarket returned an unexpected payload for %s', $path));
        }

        return $data;
    }
}
