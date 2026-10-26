<?php

declare(strict_types=1);

namespace App\Domain\Collection\RegisterOwnedCard;

use App\Domain\Collection\OwnedCard;
use App\Domain\Collection\OwnedCardRepository;
use App\Domain\Price\CardPriceRecorder;
use App\Domain\Set\CardSet;
use App\Domain\Set\CardSetRepository;
use App\Domain\TcgDex\CouldNotReachTcgDex;
use App\Domain\TcgDex\TcgDexApi;
use App\Domain\TcgDex\TcgDexLanguage;
use App\Infrastructure\CQRS\Command\Command;
use App\Infrastructure\CQRS\Command\CommandHandler;
use App\Infrastructure\CQRS\Command\CouldNotProcessCommand;

final readonly class RegisterOwnedCardCommandHandler implements CommandHandler
{
    public function __construct(
        private OwnedCardRepository $ownedCardRepository,
        private CardSetRepository $cardSetRepository,
        private TcgDexApi $tcgDexApi,
        private CardPriceRecorder $cardPriceRecorder,
    ) {
    }

    public function handle(Command $command): void
    {
        assert($command instanceof RegisterOwnedCard);

        $language = TcgDexLanguage::ENGLISH;
        $now = new \DateTimeImmutable();

        if (null !== $this->ownedCardRepository->find($command->getCardId(), $command->getVariantId())) {
            throw CouldNotProcessCommand::withReason('This card variant is already tracked.');
        }

        try {
            $card = $this->tcgDexApi->getCard($language, $command->getCardId());
        } catch (CouldNotReachTcgDex $e) {
            throw CouldNotProcessCommand::withReason($e->getMessage());
        }

        if (null === $card) {
            throw CouldNotProcessCommand::withReason(sprintf('Card %s was not found in TCGdex.', $command->getCardId()));
        }
        if (!OwnedCard::hasVariant($card, $command->getVariantId())) {
            throw CouldNotProcessCommand::withReason(sprintf('Card %s has no variant %s.', $command->getCardId(), $command->getVariantId()));
        }

        $ownedCard = OwnedCard::fromTcgDex($language, $card, $command->getVariantId(), $command->getCount(), $now);
        $this->ownedCardRepository->save($ownedCard);

        if (!$this->cardSetRepository->find($language, $ownedCard->getCardSetId())?->hasDetails()) {
            try {
                $cardSet = $this->tcgDexApi->getSet($language, $ownedCard->getCardSetId());
            } catch (CouldNotReachTcgDex $e) {
                throw CouldNotProcessCommand::withReason($e->getMessage());
            }

            if (null !== $cardSet) {
                $this->cardSetRepository->upsert(CardSet::fromTcgDex($language, $cardSet, $now));
            }
        }

        $this->cardPriceRecorder->record($language, $ownedCard->getCardId(), $card, new \DateTimeImmutable('today'));
    }
}
