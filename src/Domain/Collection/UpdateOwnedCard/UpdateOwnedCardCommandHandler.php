<?php

declare(strict_types=1);

namespace App\Domain\Collection\UpdateOwnedCard;

use App\Domain\Collection\OwnedCard;
use App\Domain\Collection\OwnedCardRepository;
use App\Infrastructure\CQRS\Command\Command;
use App\Infrastructure\CQRS\Command\CommandHandler;
use App\Infrastructure\CQRS\Command\CouldNotProcessCommand;

final readonly class UpdateOwnedCardCommandHandler implements CommandHandler
{
    public function __construct(
        private OwnedCardRepository $ownedCardRepository,
    ) {
    }

    public function handle(Command $command): void
    {
        assert($command instanceof UpdateOwnedCard);

        $ownedCard = $this->ownedCardRepository->find($command->getCardId(), $command->getVariantId());
        if (null === $ownedCard) {
            throw CouldNotProcessCommand::withReason('This card variant is not tracked.');
        }

        if ($command->getNewVariantId() === $command->getVariantId()) {
            $this->ownedCardRepository->save($ownedCard->withCount($command->getCount()));

            return;
        }

        if (!OwnedCard::hasVariant($ownedCard->getData(), $command->getNewVariantId())) {
            throw CouldNotProcessCommand::withReason(sprintf('Card %s has no variant %s.', $command->getCardId(), $command->getNewVariantId()));
        }

        $this->ownedCardRepository->delete($ownedCard);

        $target = $this->ownedCardRepository->find($command->getCardId(), $command->getNewVariantId());
        $this->ownedCardRepository->save(null === $target
            ? $ownedCard->withVariant($command->getNewVariantId())->withCount($command->getCount())
            : $target->withCount($target->getCount() + $command->getCount())
        );
    }
}
