<?php

declare(strict_types=1);

namespace App\Console;

use App\Domain\Cardmarket\CardmarketApi;
use App\Domain\Collection\OwnedCard;
use App\Domain\Collection\OwnedCardRepository;
use App\Domain\Price\CardPriceRecorder;
use App\Domain\Sealed\Owned\OwnedSealedProductRepository;
use App\Domain\Sealed\Price\SealedProductPrice;
use App\Domain\Sealed\Price\SealedProductPriceRepository;
use App\Domain\Sealed\SealedProductRepository;
use App\Domain\TcgDex\TcgDexApi;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:prices:refresh', description: 'Store today\'s TCGdex prices for every owned card and Cardmarket prices for every owned sealed product')]
final class RefreshPricesConsoleCommand extends Command
{
    public function __construct(
        private readonly OwnedCardRepository $ownedCardRepository,
        private readonly TcgDexApi $tcgDexApi,
        private readonly CardPriceRecorder $cardPriceRecorder,
        private readonly OwnedSealedProductRepository $ownedSealedProductRepository,
        private readonly SealedProductRepository $sealedProductRepository,
        private readonly SealedProductPriceRepository $sealedProductPriceRepository,
        private readonly CardmarketApi $cardmarketApi,
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

            $cardPrices = $this->cardPriceRecorder->record($ownedCard->getLanguage(), $ownedCard->getCardId(), $card, $today);
            if ($cardPrices->isEmpty()) {
                $withoutPrice[] = [$ownedCard->getCardId(), $ownedCard->getName(), 'No prices in TCGdex'];
                continue;
            }

            ++$pricedCards;
            $priceRows += count($cardPrices);
        }

        $pricedSealedProducts = 0;
        $ownedSealedProducts = $this->ownedSealedProductRepository->findAll();
        if (!$ownedSealedProducts->isEmpty()) {
            $priceGuide = $this->cardmarketApi->getPriceGuide();
            foreach ($ownedSealedProducts as $ownedSealedProduct) {
                $productId = $ownedSealedProduct->getProductId();
                $prices = $priceGuide->findPrices($productId);
                $sealedProductPrice = null === $prices ? null : SealedProductPrice::fromCardmarket(
                    productId: $productId,
                    date: $today,
                    prices: $prices,
                    sourceUpdatedOn: $priceGuide->getCreatedOn(),
                );
                if (null === $sealedProductPrice) {
                    $withoutPrice[] = [(string) $productId, $this->sealedProductRepository->find($productId)?->getName() ?? '', 'No prices on Cardmarket'];
                    continue;
                }

                $this->sealedProductPriceRepository->upsert($sealedProductPrice);
                ++$pricedSealedProducts;
            }
        }

        if ([] !== $withoutPrice) {
            $output->section('Products without prices');
            $output->table(['ID', 'Name', 'Reason'], $withoutPrice);
        }

        $output->success(sprintf(
            '%d cards priced (%d price rows) and %d sealed products priced for %s, %d without prices',
            $pricedCards,
            $priceRows,
            $pricedSealedProducts,
            $today->format('Y-m-d'),
            count($withoutPrice),
        ));

        return Command::SUCCESS;
    }
}
