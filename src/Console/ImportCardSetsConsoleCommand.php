<?php

declare(strict_types=1);

namespace App\Console;

use App\Domain\Set\CardSet;
use App\Domain\Set\CardSetRepository;
use App\Domain\TcgDex\TcgDexApi;
use App\Domain\TcgDex\TcgDexLanguage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:sets:import', description: 'Import the TCGdex set lists')]
final class ImportCardSetsConsoleCommand extends Command
{
    public function __construct(
        private readonly TcgDexApi $tcgDexApi,
        private readonly CardSetRepository $cardSetRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new SymfonyStyle($input, $output);

        foreach (TcgDexLanguage::cases() as $language) {
            $sets = $this->tcgDexApi->getSets($language);
            foreach ($sets as $set) {
                $this->cardSetRepository->upsert(CardSet::fromTcgDex($language, $set, null));
            }
            $output->writeln(sprintf('Imported %d "%s" sets', count($sets), $language->value));
        }

        $output->success('Card sets imported');

        return Command::SUCCESS;
    }
}
