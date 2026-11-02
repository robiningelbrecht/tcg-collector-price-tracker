<?php

declare(strict_types=1);

namespace App\Domain\Binder\Page\RemoveBinderPagePhoto;

use App\Domain\Binder\BinderRepository;
use App\Domain\Binder\Page\BinderPageRepository;
use App\Domain\Binder\Page\BinderPhotoStorage;
use App\Infrastructure\CQRS\Command\Command;
use App\Infrastructure\CQRS\Command\CommandHandler;
use App\Infrastructure\CQRS\Command\CouldNotProcessCommand;

final readonly class RemoveBinderPagePhotoCommandHandler implements CommandHandler
{
    public function __construct(
        private BinderRepository $binderRepository,
        private BinderPageRepository $binderPageRepository,
        private BinderPhotoStorage $binderPhotoStorage,
    ) {
    }

    public function handle(Command $command): void
    {
        assert($command instanceof RemoveBinderPagePhoto);

        $binder = $this->binderRepository->find($command->getLanguage(), $command->getCardSetId());
        if (null === $binder) {
            throw CouldNotProcessCommand::withReason('This binder does not exist.');
        }

        $page = $this->binderPageRepository->find($binder, $command->getPageNumber());
        if (null === $page) {
            throw CouldNotProcessCommand::withReason(sprintf('Page %d has no photo.', $command->getPageNumber()));
        }

        $this->binderPageRepository->delete($page);
        $this->binderPhotoStorage->delete($binder, $page->getFileName());
    }
}
