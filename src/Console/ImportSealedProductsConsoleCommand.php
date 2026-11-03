<?php

declare(strict_types=1);

namespace App\Console;

use App\Domain\Cardmarket\CardmarketApi;
use App\Domain\Sealed\SealedProduct;
use App\Domain\Sealed\SealedProductRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:sealed-products:import', description: 'Import the Cardmarket sealed product catalogue')]
final class ImportSealedProductsConsoleCommand extends Command
{
    public function __construct(
        private readonly CardmarketApi $cardmarketApi,
        private readonly SealedProductRepository $sealedProductRepository,
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new SymfonyStyle($input, $output);

        $products = $this->cardmarketApi->getSealedProducts();
        $this->connection->transactional(function () use ($products): void {
            foreach ($products as $product) {
                $this->sealedProductRepository->upsert(SealedProduct::fromCardmarket($product));
            }
        });

        $output->success(sprintf('Imported %d sealed products', count($products)));

        return Command::SUCCESS;
    }
}
