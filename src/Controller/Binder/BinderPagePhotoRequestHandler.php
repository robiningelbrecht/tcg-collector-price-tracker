<?php

declare(strict_types=1);

namespace App\Controller\Binder;

use App\Domain\Binder\Binder;
use App\Domain\Binder\BinderRepository;
use App\Domain\Binder\Page\BinderPage;
use App\Domain\Binder\Page\BinderPageRepository;
use App\Domain\Binder\Page\BinderPhotoStorage;
use App\Domain\TcgDex\TcgDexLanguage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final readonly class BinderPagePhotoRequestHandler
{
    public function __construct(
        private BinderRepository $binderRepository,
        private BinderPageRepository $binderPageRepository,
        private BinderPhotoStorage $binderPhotoStorage,
    ) {
    }

    #[Route(
        path: '/binders/{language}/{cardSetId}/pages/{pageNumber}/{fileName}',
        name: 'binder_page_photo',
        requirements: ['cardSetId' => '[^/]+', 'pageNumber' => '\d+', 'fileName' => '[^/]+'],
        methods: ['GET'],
    )]
    public function handle(string $language, string $cardSetId, int $pageNumber, string $fileName): Response
    {
        $tcgDexLanguage = TcgDexLanguage::tryFrom($language);
        $binder = null === $tcgDexLanguage ? null : $this->binderRepository->find($tcgDexLanguage, $cardSetId);
        $page = $binder instanceof Binder ? $this->binderPageRepository->find($binder, $pageNumber) : null;
        if (!$binder instanceof Binder || !$page instanceof BinderPage || $page->getFileName() !== $fileName) {
            throw new NotFoundHttpException('Binder page photo not found');
        }

        if (!$this->binderPhotoStorage->exists($binder, $page->getFileName())) {
            throw new NotFoundHttpException('Binder page photo not found');
        }

        $response = new Response(
            $this->binderPhotoStorage->read($binder, $page->getFileName()),
            headers: ['Content-Type' => $this->binderPhotoStorage->mimeType($binder, $page->getFileName())],
        );
        $response->setPrivate();
        $response->setMaxAge(31536000);
        $response->setImmutable();

        return $response;
    }
}
