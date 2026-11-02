<?php

declare(strict_types=1);

namespace App\Controller\Binder;

use App\Domain\Binder\Overview\BinderOverviewQuery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
final readonly class BindersRequestHandler
{
    public function __construct(
        private BinderOverviewQuery $binderOverviewQuery,
        private Environment $twig,
    ) {
    }

    #[Route(path: '/binders', name: 'binders', methods: ['GET'])]
    public function handle(): Response
    {
        return new Response($this->twig->render('binder/binders.html.twig', [
            'binders' => $this->binderOverviewQuery->find(),
            'setsBySerie' => $this->binderOverviewQuery->findOwnedSetsWithoutBinder(),
        ]));
    }
}
