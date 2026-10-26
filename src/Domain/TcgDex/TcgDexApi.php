<?php

declare(strict_types=1);

namespace App\Domain\TcgDex;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

final readonly class TcgDexApi
{
    private const string BASE_URI = 'https://api.tcgdex.net/v2/';
    private const int MICROSECONDS_BETWEEN_REQUESTS = 250_000;

    public function __construct(
        private Client $client,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getSets(TcgDexLanguage $language): array
    {
        /** @var list<array<string, mixed>> $sets */
        $sets = $this->get($language->value.'/sets') ?? [];

        return $sets;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCard(TcgDexLanguage $language, string $cardId): ?array
    {
        return $this->get($language->value.'/cards/'.rawurlencode($cardId));
    }

    /**
     * @return array<mixed>|null
     */
    private function get(string $path): ?array
    {
        usleep(self::MICROSECONDS_BETWEEN_REQUESTS);

        $response = $this->client->request('GET', self::BASE_URI.$path, [
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::TIMEOUT => 30,
        ]);

        if (404 === $response->getStatusCode()) {
            return null;
        }

        if ($response->getStatusCode() >= 400) {
            throw new CouldNotReachTcgDex(sprintf('TCGdex returned %d for %s', $response->getStatusCode(), $path));
        }

        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new CouldNotReachTcgDex(sprintf('TCGdex returned an unexpected payload for %s', $path));
        }

        return $data;
    }
}
