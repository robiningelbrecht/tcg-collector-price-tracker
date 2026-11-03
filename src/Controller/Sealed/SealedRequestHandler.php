<?php

declare(strict_types=1);

namespace App\Controller\Sealed;

use App\Domain\Sealed\Overview\SealedOverviewQuery;
use App\Domain\Sealed\SealedProductRepository;
use App\Domain\Sealed\SealedProducts;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
final readonly class SealedRequestHandler
{
    private const int SEARCH_LIMIT = 30;

    public function __construct(
        private SealedOverviewQuery $sealedOverviewQuery,
        private SealedProductRepository $sealedProductRepository,
        private Environment $twig,
    ) {
    }

    #[Route(path: '/sealed', name: 'sealed', methods: ['GET'])]
    public function handle(Request $request): Response
    {
        $query = trim($request->query->getString('q'));

        return new Response($this->twig->render('sealed/sealed.html.twig', [
            'products' => $this->sealedOverviewQuery->find(),
            'query' => $query,
            'searchResults' => '' === $query ? SealedProducts::empty() : $this->sealedProductRepository->search(
                query: $query,
                limit: self::SEARCH_LIMIT,
            ),
            'searchLimit' => self::SEARCH_LIMIT,
        ]));
    }
}
