<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Collection\Dashboard\CardDetail;
use App\Domain\Collection\Dashboard\PriceRange;
use App\Domain\Collection\Overview\CollectionOverviewQuery;
use App\Domain\Collection\Overview\OverviewCard;
use App\Domain\TcgDex\TcgDexLanguage;
use Symfony\Component\HttpFoundation\Request;
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
    public function handle(Request $request, string $language, string $cardId): Response
    {
        $cards = $this->collectionOverviewQuery->find();
        $card = $cards->findByCardId(TcgDexLanguage::from($language), $cardId);
        if (!$card instanceof OverviewCard) {
            throw new NotFoundHttpException(sprintf('Card %s/%s is not in the collection', $language, $cardId));
        }

        return new Response($this->twig->render('card.html.twig', [
            'detail' => CardDetail::build(
                cards: $cards,
                card: $card,
                range: PriceRange::tryFrom($request->query->getString('range'))
                    ?? PriceRange::ONE_YEAR->orLongestCoveredBy($cards->getHistoryDays()),
            ),
            'ranges' => PriceRange::cases(),
        ]));
    }
}
