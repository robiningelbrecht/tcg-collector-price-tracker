<?php

declare(strict_types=1);

namespace App\Console;

use App\Domain\Collection\OwnedCard;
use App\Domain\Collection\OwnedCardRepository;
use App\Domain\TcgCollector\CollectionHtmlParser;
use App\Domain\TcgCollector\CouldNotParseCollectionHtml;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:collection:import', description: 'Import owned cards from a saved TCG Collector collection page')]
final class ImportCollectionConsoleCommand extends Command
{
    public function __construct(
        private readonly CollectionHtmlParser $collectionHtmlParser,
        private readonly OwnedCardRepository $ownedCardRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::OPTIONAL, 'Path to the saved HTML file', 'storage/page1.html');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new SymfonyStyle($input, $output);
        /** @var string $file */
        $file = $input->getArgument('file');

        if (!is_file($file) || false === $html = file_get_contents($file)) {
            $output->error(sprintf('Could not read file "%s"', $file));

            return Command::FAILURE;
        }

        try {
            $ownedCards = $this->collectionHtmlParser->parse($html, new \DateTimeImmutable());
        } catch (CouldNotParseCollectionHtml $e) {
            $output->error($e->getMessage());

            return Command::FAILURE;
        }

        foreach ($ownedCards as $ownedCard) {
            $this->ownedCardRepository->upsert($ownedCard);
        }

        $output->success(sprintf(
            'Imported %d owned card variants (%d copies)',
            count($ownedCards),
            $ownedCards->sum(fn (OwnedCard $ownedCard): int => $ownedCard->getCount()),
        ));

        return Command::SUCCESS;
    }
}
