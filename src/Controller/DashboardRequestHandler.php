<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Collection\Overview\CollectionOverviewQuery;
use App\Domain\Collection\Overview\OverviewCard;
use App\Domain\Collection\Overview\OverviewCards;
use App\Domain\TcgDex\TcgDexLanguage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
final readonly class DashboardRequestHandler
{
    public function __construct(
        private CollectionOverviewQuery $collectionOverviewQuery,
        private Environment $twig,
    ) {
    }

    #[Route(path: '/', name: 'dashboard', methods: ['GET'])]
    public function handle(): Response
    {
        $cards = $this->collectionOverviewQuery->find();
        $dates = $cards->getPriceDates();
        $ranks = $cards->getRanks();

        $data = [
            'dates' => $dates,
            'series' => $this->buildSeries($cards),
            'cards' => $cards->map(fn (OverviewCard $card): array => $this->buildCard($card, $dates, $ranks[$card->getId()] ?? null)),
        ];

        return new Response($this->twig->render('dashboard.html.twig', [
            'lastDate' => [] === $dates ? null : new \DateTimeImmutable(end($dates)),
            'cardCount' => count($cards),
            'pricedCardCount' => count($cards->filter(fn (OverviewCard $card): bool => $card->isPriced())),
            'data' => json_encode($data, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]));
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    private function buildSeries(OverviewCards $cards): array
    {
        $series = [];
        foreach ($cards as $card) {
            $serieId = $card->getSerieId() ?? 'unknown';
            $releaseDate = $card->getSetReleaseDate() ?? '9999-12-31';
            $series[$serieId] ??= ['id' => $serieId, 'label' => $card->getSerieName() ?? $serieId, 'release' => $releaseDate];
            if (TcgDexLanguage::ENGLISH === $card->getLanguage() && null !== $card->getSerieName()) {
                $series[$serieId]['label'] = $card->getSerieName();
            }
            $series[$serieId]['release'] = min($series[$serieId]['release'], $releaseDate);
        }

        usort($series, fn (array $a, array $b): int => $a['release'] <=> $b['release']);

        return array_map(
            fn (array $serie): array => ['id' => $serie['id'], 'label' => $serie['label']],
            $series,
        );
    }

    /**
     * @param list<string> $dates
     *
     * @return array<string, mixed>
     */
    private function buildCard(OverviewCard $card, array $dates, ?int $rank): array
    {
        return [
            'id' => $card->getId(),
            'rank' => $rank,
            'name' => $card->getName(),
            'set' => $card->getSetName(),
            'num' => $card->getNumber(),
            'rarity' => $card->getRarity() ?? '',
            'img' => $card->getImage(),
            'qty' => $card->getCount(),
            'pricedQty' => $card->getPricedCount(),
            'region' => TcgDexLanguage::JAPANESE === $card->getLanguage() ? 'JP' : 'EN',
            'era' => $card->getSerieId() ?? 'unknown',
            'isNew' => $card->isFromNewSet(),
            'hist' => $card->getValuesOn($dates),
        ];
    }
}
