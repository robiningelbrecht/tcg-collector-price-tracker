<?php

declare(strict_types=1);

namespace App\Domain\Binder\Page\UploadBinderPagePhoto;

use App\Domain\Binder\BinderRepository;
use App\Domain\Binder\Page\BinderPage;
use App\Domain\Binder\Page\BinderPageRepository;
use App\Domain\Binder\Page\BinderPhotoStorage;
use App\Infrastructure\CQRS\Command\Command;
use App\Infrastructure\CQRS\Command\CommandHandler;
use App\Infrastructure\CQRS\Command\CouldNotProcessCommand;

final readonly class UploadBinderPagePhotoCommandHandler implements CommandHandler
{
    public function __construct(
        private BinderRepository $binderRepository,
        private BinderPageRepository $binderPageRepository,
        private BinderPhotoStorage $binderPhotoStorage,
    ) {
    }

    public function handle(Command $command): void
    {
        assert($command instanceof UploadBinderPagePhoto);

        $binder = $this->binderRepository->find($command->getLanguage(), $command->getCardSetId());
        if (null === $binder) {
            throw CouldNotProcessCommand::withReason('This binder does not exist.');
        }

        if ($command->getPageNumber() < 1 || $command->getPageNumber() > $binder->getPageCount()) {
            throw CouldNotProcessCommand::withReason(sprintf('This binder has no page %d.', $command->getPageNumber()));
        }

        if (!BinderPhotoStorage::supports(BinderPhotoStorage::detectMimeType($command->getPhoto()))) {
            throw CouldNotProcessCommand::withReason('Upload a JPEG, PNG, WebP or HEIC photo.');
        }

        $previous = $this->binderPageRepository->find($binder, $command->getPageNumber());
        $fileName = $this->binderPhotoStorage->store($binder, $command->getPageNumber(), $command->getPhoto());

        try {
            $this->binderPageRepository->save(BinderPage::create(
                language: $binder->getLanguage(),
                cardSetId: $binder->getCardSetId(),
                pageNumber: $command->getPageNumber(),
                fileName: $fileName,
                takenOn: $command->getTakenOn(),
                uploadedOn: new \DateTimeImmutable(),
            ));
        } catch (\Throwable $e) {
            $this->binderPhotoStorage->delete($binder, $fileName);

            throw $e;
        }

        if (null !== $previous) {
            $this->binderPhotoStorage->delete($binder, $previous->getFileName());
        }
    }
}
