<?php

declare(strict_types=1);

namespace App\Domain\Collection;

use App\Domain\Set\CardSets;
use App\Domain\Set\SetNameMap;
use App\Domain\TcgCollector\CollectionCard;
use App\Domain\TcgDex\TcgDexApi;
use App\Domain\TcgDex\TcgDexLanguage;

final readonly class CardMatcher
{
    public function __construct(
        private TcgDexApi $tcgDexApi,
        private OwnedCardRepository $ownedCardRepository,
        private SetNameMap $setNameMap,
        private ManualCardMap $manualCardMap,
    ) {
    }

    public function match(CollectionCard $collectionCard, CardSets $cardSets, \DateTimeImmutable $now): CardMatch
    {
        $language = TcgDexLanguage::ENGLISH;

        if (null !== $cardId = $this->manualCardMap->find($collectionCard->getTcgCollectorCardId())) {
            return $this->fetch($collectionCard, $language, [$cardId], $now)
                ?? CardMatch::unmatched(sprintf('Card "%s" from ManualCardMap not found in TCGdex', $cardId));
        }

        $cardSetId = $this->setNameMap->resolve($collectionCard->getSetName(), $cardSets);
        if (null === $cardSetId) {
            return CardMatch::unmatched(sprintf('Set "%s" not found in TCGdex', $collectionCard->getSetName()));
        }

        $cardIds = $this->cardIdCandidates($cardSetId, $collectionCard->getCardNumber());
        $ownedCard = $this->fetch($collectionCard, $language, $cardIds, $now)?->getOwnedCard();
        if (!$ownedCard instanceof OwnedCard) {
            return CardMatch::unmatched(sprintf('Card "%s" not found in TCGdex', implode('", "', $cardIds)));
        }

        return $this->verifyName($ownedCard, $collectionCard);
    }

    /**
     * @param list<string> $cardIds
     */
    private function fetch(CollectionCard $collectionCard, TcgDexLanguage $language, array $cardIds, \DateTimeImmutable $now): ?CardMatch
    {
        foreach ($cardIds as $cardId) {
            if (($ownedCard = $this->ownedCardRepository->findOneByCardId($language, $cardId)) instanceof OwnedCard) {
                return CardMatch::matched(OwnedCard::fromTcgDex($collectionCard, $language, $ownedCard->getData(), $now));
            }
        }

        foreach ($cardIds as $cardId) {
            if (null !== $data = $this->tcgDexApi->getCard($language, $cardId)) {
                return CardMatch::matched(OwnedCard::fromTcgDex($collectionCard, $language, $data, $now));
            }
        }

        return null;
    }

    private function verifyName(OwnedCard $ownedCard, CollectionCard $collectionCard): CardMatch
    {
        if ($this->normalizeName($collectionCard->getName()) !== $this->normalizeName($ownedCard->getName())) {
            return CardMatch::unmatched(sprintf('Name mismatch, TCGdex card "%s" is "%s"', $ownedCard->getCardId(), $ownedCard->getName()));
        }

        return CardMatch::matched($ownedCard);
    }

    /**
     * @return list<string>
     */
    private function cardIdCandidates(string $cardSetId, string $cardNumber): array
    {
        $number = trim((string) preg_replace('/^No\.\s*/i', '', $cardNumber));
        $localId = explode('/', $number)[0];

        if (preg_match('/^([A-Z]{2})\d+$/', $localId, $matches)) {
            return [
                $cardSetId.'-'.$localId,
                $cardSetId.strtolower($matches[1]).'-'.$localId,
            ];
        }

        if (!ctype_digit($localId)) {
            return [$cardSetId.'-'.$localId];
        }

        $withoutLeadingZeros = ltrim($localId, '0') ?: '0';

        return array_values(array_unique(array_map(
            fn (string $candidate): string => $cardSetId.'-'.$candidate,
            [$localId, $withoutLeadingZeros, str_pad($withoutLeadingZeros, 3, '0', STR_PAD_LEFT)],
        )));
    }

    private function normalizeName(string $name): string
    {
        $name = str_replace(['’', 'é'], ["'", 'e'], mb_strtolower($name));
        $name = (string) preg_replace('/\s*(δ|lv\.\s*x)\s*/u', ' ', $name);

        return trim((string) preg_replace('/\s+/', ' ', $name));
    }
}
