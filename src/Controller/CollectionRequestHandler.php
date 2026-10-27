<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Collection\Dashboard\CollectionDashboard;
use App\Domain\Collection\Dashboard\MoverMode;
use App\Domain\Collection\Dashboard\PriceRange;
use App\Domain\Collection\Overview\CollectionOverviewQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
final readonly class CollectionRequestHandler
{
    public function __construct(
        private CollectionOverviewQuery $collectionOverviewQuery,
        private Environment $twig,
    ) {
    }

    #[Route(path: '/collection', name: 'collection', methods: ['GET'])]
    public function handle(Request $request): Response
    {
        $overviewCards = $this->collectionOverviewQuery->find();
        $dashboard = CollectionDashboard::build(
            overviewCards: $overviewCards,
            range: PriceRange::tryFrom($request->query->getString('range'))
                ?? PriceRange::NINETY_DAYS->orLongestCoveredBy($overviewCards->getHistoryDays()),
            moverMode: MoverMode::PERCENTAGE,
        );

        return new Response($this->twig->render('collection.html.twig', [
            'dashboard' => $dashboard,
            'ranges' => PriceRange::cases(),
        ]));
    }
}
