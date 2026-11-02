<?php

declare(strict_types=1);

namespace App\Controller\Binder;

use App\Domain\Binder\BinderRepository;
use App\Domain\Binder\Page\BinderPageRepository;
use App\Domain\Binder\Page\BinderPhotoStorage;
use App\Domain\Set\CardSetRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final readonly class ExportBinderPhotosRequestHandler
{
    public function __construct(
        private BinderRepository $binderRepository,
        private BinderPageRepository $binderPageRepository,
        private CardSetRepository $cardSetRepository,
        private BinderPhotoStorage $binderPhotoStorage,
    ) {
    }

    #[Route(path: '/binders/export', name: 'export_binder_photos', methods: ['GET'])]
    public function handle(): BinaryFileResponse
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'binders');
        $zip = new \ZipArchive();
        if (false === $zipPath || true !== $zip->open($zipPath, \ZipArchive::OVERWRITE)) {
            throw new \RuntimeException('Could not create the export archive');
        }

        $photoCount = 0;
        foreach ($this->binderRepository->findAll() as $binder) {
            $folder = $this->folderName($this->cardSetRepository->find($binder->getLanguage(), $binder->getCardSetId())?->getName() ?? $binder->getCardSetId());
            foreach ($this->binderPageRepository->findByBinder($binder) as $page) {
                if (!$this->binderPhotoStorage->exists($binder, $page->getFileName())) {
                    continue;
                }

                $name = sprintf('%s/page-%03d.%s', $folder, $page->getPageNumber(), pathinfo($page->getFileName(), PATHINFO_EXTENSION));
                $zip->addFromString($name, $this->binderPhotoStorage->read($binder, $page->getFileName()));
                $zip->setCompressionName($name, \ZipArchive::CM_STORE);
                $zip->setMtimeName($name, $page->getTakenOn()->getTimestamp());
                ++$photoCount;
            }
        }

        if (0 === $photoCount) {
            $zip->close();
            unlink($zipPath);
            throw new NotFoundHttpException('There are no binder photos to export');
        }
        $zip->close();

        $response = new BinaryFileResponse($zipPath, headers: ['Content-Type' => 'application/zip']);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, sprintf('binder-photos-%s.zip', new \DateTimeImmutable()->format('Y-m-d')));
        $response->deleteFileAfterSend();

        return $response;
    }

    private function folderName(string $setName): string
    {
        return trim((string) preg_replace('/[\/\\\\:*?"<>|]+/', '-', $setName), ' .-') ?: 'Binder';
    }
}
