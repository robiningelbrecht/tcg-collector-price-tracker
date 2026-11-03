<?php

declare(strict_types=1);

namespace App\Controller\Sealed;

use App\Domain\Sealed\Overview\SealedOverviewQuery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
final readonly class SealedRequestHandler
{
    public function __construct(
        private SealedOverviewQuery $sealedOverviewQuery,
        private Environment $twig,
    ) {
    }

    #[Route(path: '/sealed', name: 'sealed', methods: ['GET'])]
    public function handle(): Response
    {
        return new Response($this->twig->render('sealed/sealed.html.twig', [
            'products' => $this->sealedOverviewQuery->find(),
        ]));
    }
}
