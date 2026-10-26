<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Collection\OwnedCardRepository;
use App\Domain\Collection\RegisterOwnedCard\RegistrableCard;
use App\Domain\Collection\RegisterOwnedCard\RegistrableVariant;
use App\Domain\Price\CardPriceExtractor;
use App\Domain\Set\CardSet;
use App\Domain\Set\CardSetRepository;
use App\Domain\TcgDex\TcgDexApi;
use App\Domain\TcgDex\TcgDexLanguage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
final readonly class RegisterOwnedCardRequestHandler
{
    public function __construct(
        private CardSetRepository $cardSetRepository,
        private OwnedCardRepository $ownedCardRepository,
        private TcgDexApi $tcgDexApi,
        private CardPriceExtractor $cardPriceExtractor,
        private Environment $twig,
    ) {
    }

    #[Route(path: '/cards/register', name: 'register_owned_card', methods: ['GET'], priority: 10)]
    public function handle(Request $request): Response
    {
        $language = TcgDexLanguage::ENGLISH;
        $cardSets = $this->cardSetRepository->findAll($language);

        $cardId = $request->query->getString('card');
        if ('' !== $cardId) {
            $card = $this->tcgDexApi->getCard($language, $cardId);
            if (null === $card) {
                throw new NotFoundHttpException(sprintf('Card %s was not found in TCGdex', $cardId));
            }
            /** @var array{id?: string} $set */
            $set = is_array($card['set'] ?? null) ? $card['set'] : [];

            return new Response($this->twig->render('register-owned-card.html.twig', [
                'cardSets' => $cardSets,
                'selectedSet' => $cardSets->findById($set['id'] ?? ''),
                'card' => $card,
                'variants' => RegistrableVariant::listFromTcgDex(
                    card: $card,
                    ownedCards: $this->ownedCardRepository->findByCardId($language, $cardId),
                    cardPrices: $this->cardPriceExtractor->extract($language, $cardId, $card, new \DateTimeImmutable('today')),
                ),
            ]));
        }

        $cardSetId = $request->query->getString('set');
        if ('' === $cardSetId) {
            return new Response($this->twig->render('register-owned-card.html.twig', [
                'cardSets' => $cardSets,
            ]));
        }

        $selectedSet = $cardSets->findById($cardSetId);
        if (!$selectedSet instanceof CardSet || null === $set = $this->tcgDexApi->getSet($language, $cardSetId)) {
            throw new NotFoundHttpException(sprintf('Set %s was not found', $cardSetId));
        }

        return new Response($this->twig->render('register-owned-card.html.twig', [
            'cardSets' => $cardSets,
            'selectedSet' => $selectedSet,
            'cards' => RegistrableCard::listFromTcgDexSet($set, $this->ownedCardRepository->findByCardSetId($language, $cardSetId)),
        ]));
    }
}
