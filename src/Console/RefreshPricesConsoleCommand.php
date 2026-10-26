<?php

declare(strict_types=1);

namespace App\Console;

use App\Domain\Collection\OwnedCard;
use App\Domain\Collection\OwnedCardRepository;
use App\Domain\Price\CardPriceExtractor;
use App\Domain\Price\CardPriceRepository;
use App\Domain\TcgDex\TcgDexApi;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:prices:refresh', description: 'Store today\'s TCGdex prices for every owned card')]
final class RefreshPricesConsoleCommand extends Command
{
    public function __construct(
        private readonly OwnedCardRepository $ownedCardRepository,
        private readonly TcgDexApi $tcgDexApi,
        private readonly CardPriceExtractor $cardPriceExtractor,
        private readonly CardPriceRepository $cardPriceRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new SymfonyStyle($input, $output);
        $today = new \DateTimeImmutable('today');

        $cards = [];
        /** @var OwnedCard $ownedCard */
        foreach ($this->ownedCardRepository->findAll() as $ownedCard) {
            $cards[$ownedCard->getLanguage()->value.'|'.$ownedCard->getCardId()] = $ownedCard;
        }

        $pricedCards = 0;
        $priceRows = 0;
        $withoutPrice = [];

        foreach ($output->progressIterate($cards) as $ownedCard) {
            $card = $this->tcgDexApi->getCard($ownedCard->getLanguage(), $ownedCard->getCardId());
            if (null === $card) {
                $withoutPrice[] = [$ownedCard->getCardId(), $ownedCard->getName(), 'Card not found in TCGdex'];
                continue;
            }

            $cardPrices = $this->cardPriceExtractor->extract($ownedCard->getLanguage(), $ownedCard->getCardId(), $card, $today);
            if ($cardPrices->isEmpty()) {
                $withoutPrice[] = [$ownedCard->getCardId(), $ownedCard->getName(), 'No prices in TCGdex'];
                continue;
            }

            $this->cardPriceRepository->deleteForCardOnDate($ownedCard->getLanguage(), $ownedCard->getCardId(), $today);
            foreach ($cardPrices as $cardPrice) {
                $this->cardPriceRepository->upsert($cardPrice);
            }
            ++$pricedCards;
            $priceRows += count($cardPrices);
        }

        if ([] !== $withoutPrice) {
            $output->section('Cards without prices');
            $output->table(['TCGdex ID', 'Name', 'Reason'], $withoutPrice);
        }

        $output->success(sprintf(
            '%d cards priced (%d price rows) for %s, %d without prices',
            $pricedCards,
            $priceRows,
            $today->format('Y-m-d'),
            count($withoutPrice),
        ));

        return Command::SUCCESS;
    }
}
