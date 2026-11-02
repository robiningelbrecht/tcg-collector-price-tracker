<?php

declare(strict_types=1);

namespace App\Domain\Binder\RemoveBinder;

use App\Domain\Binder\BinderRepository;
use App\Domain\Binder\Page\BinderPageRepository;
use App\Domain\Binder\Page\BinderPhotoStorage;
use App\Infrastructure\CQRS\Command\Command;
use App\Infrastructure\CQRS\Command\CommandHandler;
use App\Infrastructure\CQRS\Command\CouldNotProcessCommand;

final readonly class RemoveBinderCommandHandler implements CommandHandler
{
    public function __construct(
        private BinderRepository $binderRepository,
        private BinderPageRepository $binderPageRepository,
        private BinderPhotoStorage $binderPhotoStorage,
    ) {
    }

    public function handle(Command $command): void
    {
        assert($command instanceof RemoveBinder);

        $binder = $this->binderRepository->find($command->getLanguage(), $command->getCardSetId());
        if (null === $binder) {
            throw CouldNotProcessCommand::withReason('This binder does not exist.');
        }

        foreach ($this->binderPageRepository->findByBinder($binder) as $page) {
            $this->binderPageRepository->delete($page);
        }
        $this->binderRepository->delete($binder);
        $this->binderPhotoStorage->deleteAll($binder);
    }
}
