<?php

declare(strict_types=1);

namespace App\Controller\Binder;

use App\Domain\Binder\Binder;
use App\Domain\Binder\BinderRepository;
use App\Domain\Binder\Page\BinderPageRepository;
use App\Domain\Set\CardSetRepository;
use App\Domain\TcgDex\TcgDexLanguage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
final readonly class BinderRequestHandler
{
    public function __construct(
        private BinderRepository $binderRepository,
        private BinderPageRepository $binderPageRepository,
        private CardSetRepository $cardSetRepository,
        private Environment $twig,
    ) {
    }

    #[Route(path: '/binders/{language}/{cardSetId}', name: 'binder', requirements: ['cardSetId' => '[^/]+'], methods: ['GET'])]
    public function handle(string $language, string $cardSetId): Response
    {
        $tcgDexLanguage = TcgDexLanguage::tryFrom($language);
        $binder = null === $tcgDexLanguage ? null : $this->binderRepository->find($tcgDexLanguage, $cardSetId);
        if (!$binder instanceof Binder) {
            throw new NotFoundHttpException(sprintf('No binder for set %s/%s', $language, $cardSetId));
        }

        return new Response($this->twig->render('binder/binder.html.twig', [
            'binder' => $binder,
            'cardSet' => $this->cardSetRepository->find($binder->getLanguage(), $binder->getCardSetId()),
            'binderPages' => $this->binderPageRepository->findByBinder($binder),
        ]));
    }
}
