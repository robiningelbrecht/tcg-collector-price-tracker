<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Binder\Binder;
use App\Domain\Binder\BinderRepository;
use App\Domain\Binder\Page\BinderPage;
use App\Domain\Binder\Page\BinderPageRepository;
use App\Domain\Binder\Page\BinderPhotoStorage;
use App\Domain\TcgDex\TcgDexLanguage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
    public function handle(string $language, string $cardSetId, int $pageNumber, string $fileName): BinaryFileResponse
    {
        $tcgDexLanguage = TcgDexLanguage::tryFrom($language);
        $binder = null === $tcgDexLanguage ? null : $this->binderRepository->find($tcgDexLanguage, $cardSetId);
        $page = $binder instanceof Binder ? $this->binderPageRepository->find($binder, $pageNumber) : null;
        if (!$binder instanceof Binder || !$page instanceof BinderPage || $page->getFileName() !== $fileName) {
            throw new NotFoundHttpException('Binder page photo not found');
        }

        $path = $this->binderPhotoStorage->path($binder, $page->getFileName());
        if (!is_file($path)) {
            throw new NotFoundHttpException('Binder page photo not found');
        }

        $response = new BinaryFileResponse($path, headers: ['Content-Type' => BinderPhotoStorage::detectMimeType($path)]);
        $response->setPrivate();
        $response->setMaxAge(31536000);
        $response->setImmutable();

        return $response;
    }
}
