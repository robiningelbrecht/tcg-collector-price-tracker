<?php

declare(strict_types=1);

namespace App\Domain\Collection\RemoveOwnedCard;

use App\Domain\Collection\OwnedCardRepository;
use App\Infrastructure\CQRS\Command\Command;
use App\Infrastructure\CQRS\Command\CommandHandler;
use App\Infrastructure\CQRS\Command\CouldNotProcessCommand;

final readonly class RemoveOwnedCardCommandHandler implements CommandHandler
{
    public function __construct(
        private OwnedCardRepository $ownedCardRepository,
    ) {
    }

    public function handle(Command $command): void
    {
        assert($command instanceof RemoveOwnedCard);

        $ownedCard = $this->ownedCardRepository->find($command->getCardId(), $command->getVariantId());
        if (null === $ownedCard) {
            throw CouldNotProcessCommand::withReason('This card variant is not tracked.');
        }

        $this->ownedCardRepository->delete($ownedCard);
    }
}
