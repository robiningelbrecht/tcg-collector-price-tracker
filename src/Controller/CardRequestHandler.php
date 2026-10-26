<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Collection\Overview\CollectionOverviewQuery;
use App\Domain\Collection\Overview\OverviewCard;
use App\Domain\TcgDex\TcgDexLanguage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
final readonly class CardRequestHandler
{
    public function __construct(
        private CollectionOverviewQuery $collectionOverviewQuery,
        private Environment $twig,
    ) {
    }

    #[Route(path: '/cards/{language}/{cardId}', name: 'card', requirements: ['language' => 'en|ja', 'cardId' => '[^/]+'], methods: ['GET'])]
    public function handle(string $language, string $cardId): Response
    {
        $cards = $this->collectionOverviewQuery->find();
        $card = $cards->findByCardId(TcgDexLanguage::from($language), $cardId);
        if (!$card instanceof OverviewCard) {
            throw new NotFoundHttpException(sprintf('Card %s/%s is not in the collection', $language, $cardId));
        }

        $dates = $cards->getPriceDates();

        return new Response($this->twig->render('card.html.twig', [
            'card' => $card,
            'rank' => $cards->getRanks()[$card->getId()] ?? null,
            'history' => json_encode([
                'dates' => $dates,
                'hist' => $card->getValuesOn($dates),
            ], JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP),
        ]));
    }
}
