<?php

declare(strict_types=1);

namespace App\Console;

use App\Domain\Collection\CardMatcher;
use App\Domain\Collection\ManualPriceVariantMap;
use App\Domain\Collection\OwnedCard;
use App\Domain\Collection\OwnedCardRepository;
use App\Domain\Collection\PriceVariantResolver;
use App\Domain\Set\CardSet;
use App\Domain\Set\CardSetRepository;
use App\Domain\TcgCollector\CollectionHtmlParser;
use App\Domain\TcgCollector\CouldNotParseCollectionHtml;
use App\Domain\TcgDex\TcgDexApi;
use App\Domain\TcgDex\TcgDexLanguage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:collection:import', description: 'Import owned cards from a saved TCG Collector collection page and match them to TCGdex')]
final class ImportCollectionConsoleCommand extends Command
{
    public function __construct(
        private readonly CollectionHtmlParser $collectionHtmlParser,
        private readonly CardMatcher $cardMatcher,
        private readonly OwnedCardRepository $ownedCardRepository,
        private readonly CardSetRepository $cardSetRepository,
        private readonly TcgDexApi $tcgDexApi,
        private readonly PriceVariantResolver $priceVariantResolver,
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
            $collectionCards = $this->collectionHtmlParser->parse($html);
        } catch (CouldNotParseCollectionHtml $e) {
            $output->error($e->getMessage());

            return Command::FAILURE;
        }

        if (0 === $this->cardSetRepository->count()) {
            $output->error('No TCGdex sets found, run app:sets:import first');

            return Command::FAILURE;
        }

        $cardSets = [];
        foreach (TcgDexLanguage::cases() as $language) {
            $cardSets[$language->value] = $this->cardSetRepository->findAllForLanguage($language);
        }

        $now = new \DateTimeImmutable();
        $alreadyMatched = 0;
        $newlyMatched = 0;
        $unmatched = [];
        $withoutPriceVariant = [];

        foreach ($output->progressIterate($collectionCards) as $collectionCard) {
            $ownedCard = $this->ownedCardRepository->find(
                $collectionCard->getTcgCollectorCardId(),
                $collectionCard->getVariantTypeId(),
                $collectionCard->getLanguageId(),
            );
            if ($ownedCard instanceof OwnedCard) {
                $ownedCard = $this->withPriceVariant($ownedCard->withCount($collectionCard->getCount(), $now), $withoutPriceVariant);
                $this->ownedCardRepository->upsert($ownedCard);
                ++$alreadyMatched;
                continue;
            }

            $language = TcgDexLanguage::fromTcgRegion($collectionCard->getRegion());
            $match = $this->cardMatcher->match($collectionCard, $cardSets[$language->value], $now);
            $matchedCard = $match->getOwnedCard();
            if (!$matchedCard instanceof OwnedCard) {
                $unmatched[] = [
                    $collectionCard->getName(),
                    $collectionCard->getSetName(),
                    $collectionCard->getCardNumber(),
                    $collectionCard->getVariantName(),
                    $match->getReason(),
                ];
                continue;
            }

            $this->ownedCardRepository->upsert($this->withPriceVariant($matchedCard, $withoutPriceVariant));
            $this->importCardSetDetails($language, $matchedCard->getCardSetId(), $now);
            ++$newlyMatched;
        }

        if ([] !== $unmatched) {
            $output->section('Unmatched cards');
            $output->table(['Name', 'Set', 'Number', 'Variant', 'Reason'], $unmatched);
        }

        if ([] !== $withoutPriceVariant) {
            $output->section('Cards without price variant, add them to ManualPriceVariantMap');
            $output->table(['Map key', 'Name', 'TCGdex ID', 'Variant', 'TCGdex variants'], $withoutPriceVariant);
        }

        $output->success(sprintf(
            '%d already matched, %d newly matched, %d unmatched',
            $alreadyMatched,
            $newlyMatched,
            count($unmatched),
        ));

        return Command::SUCCESS;
    }

    /**
     * @param list<array<int, string>> $withoutPriceVariant
     */
    private function withPriceVariant(OwnedCard $ownedCard, array &$withoutPriceVariant): OwnedCard
    {
        $ownedCard = $ownedCard->withPriceVariantId($this->priceVariantResolver->resolve($ownedCard));
        if (null !== $ownedCard->getPriceVariantId()) {
            return $ownedCard;
        }

        $withoutPriceVariant[] = [
            ManualPriceVariantMap::key($ownedCard->getTcgCollectorCardId(), $ownedCard->getVariantTypeId()),
            $ownedCard->getName(),
            $ownedCard->getCardId(),
            $ownedCard->getVariantName(),
            implode("\n", array_map(
                fn (array $variant): string => sprintf(
                    '%s: %s',
                    $variant['variantId'],
                    implode(' ', array_filter([$variant['type'], $variant['subtype'], $variant['size'], implode(',', $variant['stamps'])])),
                ),
                $this->priceVariantResolver->variants($ownedCard),
            )),
        ];

        return $ownedCard;
    }

    private function importCardSetDetails(TcgDexLanguage $language, string $cardSetId, \DateTimeImmutable $now): void
    {
        if ($this->cardSetRepository->find($language, $cardSetId)?->hasDetails()) {
            return;
        }

        if (null === $data = $this->tcgDexApi->getSet($language, $cardSetId)) {
            return;
        }

        $this->cardSetRepository->upsert(CardSet::fromTcgDex($language, $data, $now));
    }
}
