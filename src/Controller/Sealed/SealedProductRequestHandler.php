<?php

declare(strict_types=1);

namespace App\Controller\Sealed;

use App\Domain\Sealed\Overview\SealedOverviewProduct;
use App\Domain\Sealed\Overview\SealedOverviewQuery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
final readonly class SealedProductRequestHandler
{
    public function __construct(
        private SealedOverviewQuery $sealedOverviewQuery,
        private Environment $twig,
    ) {
    }

    #[Route(path: '/sealed/{productId}', name: 'sealed_product', requirements: ['productId' => '\d+'], methods: ['GET'])]
    public function handle(int $productId): Response
    {
        $product = $this->sealedOverviewQuery->find()->findByProductId($productId);
        if (!$product instanceof SealedOverviewProduct) {
            throw new NotFoundHttpException(sprintf('Sealed product %d is not in the collection', $productId));
        }

        return new Response($this->twig->render('sealed/product.html.twig', [
            'product' => $product,
        ]));
    }
}
