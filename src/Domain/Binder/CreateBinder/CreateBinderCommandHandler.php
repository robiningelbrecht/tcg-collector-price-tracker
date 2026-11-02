<?php

declare(strict_types=1);

namespace App\Domain\Binder\CreateBinder;

use App\Domain\Binder\Binder;
use App\Domain\Binder\BinderRepository;
use App\Domain\Set\CardSetRepository;
use App\Infrastructure\CQRS\Command\Command;
use App\Infrastructure\CQRS\Command\CommandHandler;
use App\Infrastructure\CQRS\Command\CouldNotProcessCommand;

final readonly class CreateBinderCommandHandler implements CommandHandler
{
    public function __construct(
        private BinderRepository $binderRepository,
        private CardSetRepository $cardSetRepository,
    ) {
    }

    public function handle(Command $command): void
    {
        assert($command instanceof CreateBinder);

        $cardSet = $this->cardSetRepository->find($command->getLanguage(), $command->getCardSetId());
        if (null === $cardSet) {
            throw CouldNotProcessCommand::withReason(sprintf('Set %s is not known.', $command->getCardSetId()));
        }

        if (null !== $this->binderRepository->find($command->getLanguage(), $command->getCardSetId())) {
            throw CouldNotProcessCommand::withReason(sprintf('%s already has a binder.', $cardSet->getName()));
        }

        $this->binderRepository->save(Binder::create(
            language: $command->getLanguage(),
            cardSetId: $command->getCardSetId(),
            pageCount: $command->getPageCount(),
            pocketsPerPage: $command->getPocketsPerPage(),
            createdOn: new \DateTimeImmutable(),
        ));
    }
}
