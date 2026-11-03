<?php

declare(strict_types=1);

namespace App\Controller\Sealed;

use App\Domain\Sealed\Owned\OwnedSealedProduct;
use App\Domain\Sealed\Owned\OwnedSealedProductRepository;
use App\Domain\Sealed\SealedProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
final readonly class SearchSealedProductsRequestHandler
{
    private const int SEARCH_LIMIT = 30;

    public function __construct(
        private SealedProductRepository $sealedProductRepository,
        private OwnedSealedProductRepository $ownedSealedProductRepository,
        private Environment $twig,
    ) {
    }

    #[Route(path: '/sealed/search', name: 'search_sealed_products', methods: ['GET'])]
    public function handle(Request $request): Response
    {
        $query = trim($request->query->getString('q'));

        return new Response($this->twig->render('sealed/_search-results.html.twig', [
            'query' => $query,
            'searchResults' => $this->sealedProductRepository->search(
                query: $query,
                limit: self::SEARCH_LIMIT,
            ),
            'searchLimit' => self::SEARCH_LIMIT,
            'ownedProductIds' => $this->ownedSealedProductRepository->findAll()->map(
                fn (OwnedSealedProduct $ownedSealedProduct): int => $ownedSealedProduct->getProductId(),
            ),
        ]));
    }
}
