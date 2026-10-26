<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Collection\OwnedCard;
use App\Domain\Collection\OwnedCardRepository;
use App\Domain\Collection\RegisterOwnedCard\RegistrableVariant;
use App\Domain\Price\CardPrices;
use App\Domain\Set\CardSetRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
final readonly class EditOwnedCardRequestHandler
{
    public function __construct(
        private OwnedCardRepository $ownedCardRepository,
        private CardSetRepository $cardSetRepository,
        private Environment $twig,
    ) {
    }

    #[Route(path: '/cards/{cardId}/variants/{variantId}/edit', name: 'edit_owned_card', requirements: ['cardId' => '[^/]+', 'variantId' => '[^/]+'], methods: ['GET'])]
    public function handle(string $cardId, string $variantId): Response
    {
        $ownedCard = $this->ownedCardRepository->find($cardId, $variantId);
        if (!$ownedCard instanceof OwnedCard) {
            throw new NotFoundHttpException(sprintf('Card %s variant %s is not tracked', $cardId, $variantId));
        }

        $ownedVariants = $this->ownedCardRepository->findByCardId($ownedCard->getLanguage(), $cardId);
        $ownedCounts = [];
        /** @var OwnedCard $ownedVariant */
        foreach ($ownedVariants as $ownedVariant) {
            $ownedCounts[$ownedVariant->getPriceVariantId()] = $ownedVariant->getCount();
        }

        return new Response($this->twig->render('edit-owned-card.html.twig', [
            'ownedCard' => $ownedCard,
            'cardSet' => $this->cardSetRepository->find($ownedCard->getLanguage(), $ownedCard->getCardSetId()),
            'variants' => RegistrableVariant::listFromTcgDex($ownedCard->getData(), $ownedVariants, CardPrices::empty()),
            'ownedCounts' => $ownedCounts,
        ]));
    }
}
