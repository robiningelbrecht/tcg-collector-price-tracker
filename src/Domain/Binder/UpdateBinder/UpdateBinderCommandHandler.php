<?php

declare(strict_types=1);

namespace App\Domain\Binder\UpdateBinder;

use App\Domain\Binder\BinderRepository;
use App\Domain\Binder\Page\BinderPage;
use App\Domain\Binder\Page\BinderPageRepository;
use App\Infrastructure\CQRS\Command\Command;
use App\Infrastructure\CQRS\Command\CommandHandler;
use App\Infrastructure\CQRS\Command\CouldNotProcessCommand;

final readonly class UpdateBinderCommandHandler implements CommandHandler
{
    public function __construct(
        private BinderRepository $binderRepository,
        private BinderPageRepository $binderPageRepository,
    ) {
    }

    public function handle(Command $command): void
    {
        assert($command instanceof UpdateBinder);

        $binder = $this->binderRepository->find($command->getLanguage(), $command->getCardSetId());
        if (null === $binder) {
            throw CouldNotProcessCommand::withReason('This binder does not exist.');
        }

        $pagesOutOfRange = $this->binderPageRepository->findByBinder($binder)
            ->filter(fn (BinderPage $page): bool => $page->getPageNumber() > $command->getPageCount());
        if (!$pagesOutOfRange->isEmpty()) {
            throw CouldNotProcessCommand::withReason(sprintf('Page %s still has a photo. Remove it first.', implode(', ', $pagesOutOfRange->map(fn (BinderPage $page): int => $page->getPageNumber()))));
        }

        $this->binderRepository->save($binder
            ->withPageCount($command->getPageCount())
            ->withPocketsPerPage($command->getPocketsPerPage())
        );
    }
}
