<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Collection\Dashboard\CardDetail;
use App\Domain\Collection\Dashboard\PriceRange;
use App\Domain\Collection\Overview\CollectionOverviewQuery;
use App\Domain\Collection\Overview\OverviewCard;
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

    #[Route(path: '/cards/{cardId}', name: 'card', requirements: ['cardId' => '[^/]+'], methods: ['GET'])]
    public function handle(Request $request, string $cardId): Response
    {
        $cards = $this->collectionOverviewQuery->find();
        $card = $cards->findByCardId($cardId);
        if (!$card instanceof OverviewCard) {
            throw new NotFoundHttpException(sprintf('Card %s is not in the collection', $cardId));
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
