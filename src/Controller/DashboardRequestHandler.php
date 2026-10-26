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
final readonly class DashboardRequestHandler
{
    public function __construct(
        private CollectionOverviewQuery $collectionOverviewQuery,
        private Environment $twig,
    ) {
    }

    #[Route(path: '/', name: 'dashboard', methods: ['GET'])]
    public function handle(Request $request): Response
    {
        $dashboard = CollectionDashboard::build(
            overviewCards: $this->collectionOverviewQuery->find(),
            range: PriceRange::tryFrom($request->query->getString('range')) ?? PriceRange::NINETY_DAYS,
            moverMode: MoverMode::tryFrom($request->query->getString('mode')) ?? MoverMode::PERCENTAGE,
        );

        return new Response($this->twig->render('dashboard.html.twig', [
            'dashboard' => $dashboard,
            'ranges' => PriceRange::cases(),
            'moverModes' => MoverMode::cases(),
        ]));
    }
}
