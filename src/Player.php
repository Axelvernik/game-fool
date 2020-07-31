<?php
namespace kymbrik;

use SebastianBergmann\CodeCoverage\Report\PHP;

class Player
{
    private const CARDS_LIMIT = 6;

    /**
     * @var $name string
     */
    private $name;

    /**
     * @var $cards Card[]
     */
    private $cards = [];

    /**
     * @var bool
     */
    private $isOutOfTheGame = false;

    /**
     * @var bool Побежден в одной партии
     */
    private $defeated = false;

    /**
     * Player constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }


    public function printCards(): void
    {
        echo "{$this->name}(";
        foreach ($this->cards AS $card)
        {
            echo "{$card->getRank()->getValue()}{$card->getSuitName()}," ;
        }
        echo ")" ;
    }

    /**
     * @return Card[]
     */
    private function getTrumpCardIndexes(): array
    {
        $result = [];
        foreach ($this->cards AS $index => $card)
        {
            if($card->isTrump())
            {
                $result[] = $index;
            }
        }

        return $result;
    }

    /**
     * @return Card[]
     */
    private function getSimpleCardIndexes(): array
    {
        $result = [];
        foreach ($this->cards AS $index => $card)
        {
            if(!$card->isTrump())
            {
                $result[] = $index;
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isOutOfTheGame(): bool
    {
        return $this->isOutOfTheGame;
    }

    public function hasEnoughCards(): bool
    {
        return count($this->cards) >= self::CARDS_LIMIT;
    }

    /**
     * @param bool $isOutOfTheGame
     */
    public function setIsOutOfTheGame(bool $isOutOfTheGame): void
    {
        $this->isOutOfTheGame = $isOutOfTheGame;
    }

    /**
     * @return bool
     */
    public function isDefeated(): bool
    {
        return $this->defeated;
    }

    /**
     * @param bool $defeated
     */
    public function setDefeated(bool $defeated): void
    {
        $this->defeated = $defeated;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function takeCard(Card $card): void
    {
     //   echo "{$this->name} + {$card->getRank()->getValue()}{$card->getSuitName()}" . PHP_EOL;

        $this->cards[] = $card;
    }

    /**
     * Вначале идут карты всех не козырных мастей, сортированные по достоинству и по масти
     * (порядок: пика, крест, бубен, червей), затем козыри,
     * также сортированные по достоинству;
     */
    public function sortCards(string $trumpSuitName)
    {
        $this->cards = CardSorter::sort($trumpSuitName, $this->cards);
    }

    /**
     * Ход всегда начинается с самой младшей карты любой масти, кроме козыря.
     * Если в руках не осталось карт,
     * кроме козырей, то ход начинается с самого младшего козыря;
     *
     * Продолжать ход необходимо картами с достоинствами, которые использовались за ход ($battleFieldCards).
     *
     * @param Card[] $battleFieldCards
     * @return Card|null
     * @throws PlayerCardsException
     */
    public function findCardToFight(array $battleFieldCards): ?Card
    {
        if(count($this->cards) == 0) {
            return null;
        }

        // Только начало боя
        if(count($battleFieldCards) == 0) {
            return $this->findFirstCardToFight();
        } else {
            $cardIndex = $this->findNextCardIndexToFight($battleFieldCards);
            if(!is_null($cardIndex))
            {
                $card = $this->cards[$cardIndex];
                unset($this->cards[$cardIndex]);
                return $card;
            }


        }

        return null;
    }

    /**
     *
     *
     * @param Card[] $battleFieldCards
     */
    private function findNextCardIndexToFight(array $battleFieldCards): ?int
    {
        $trumpCardIndexes = $this->getTrumpCardIndexes();
        $simpleCardIndexes = $this->getSimpleCardIndexes();

        $simpleCardIndexToFight = $this->findCardIndexWithTheSameRank($battleFieldCards, $simpleCardIndexes);
        if( !is_null($simpleCardIndexToFight))
        {
            return $simpleCardIndexToFight;
        }

        $trumpCardIndexToFight = $this->findCardIndexWithTheSameRank($battleFieldCards, $trumpCardIndexes);
        // По идее тут минимальный козырь, потому что карты отсортированы и идем по порядку
        if(!is_null($trumpCardIndexToFight))
        {
            // Но может быть такое, что козырь совпавший с полем боя старший в руках игрока.
            // Поэтому проверяем, что у игрока этот козырь не самый старший
            if(count($this->cards)  == 1)
            {
                return $trumpCardIndexToFight;
            }
            // Однако нельзя продолжать ход самой старшей козырной картой в руках, если в руках более одной карты.
            if(count($this->cards) > 1 && $this->getMaxTrumpIndex() != $trumpCardIndexToFight)
            {
                return $trumpCardIndexToFight;
            }
        }

        return null;
    }

    /**
     * @param Card[] $battleFieldCards
     * @param Card[] $playerCards
     * @return int|null
     */
    private function findCardIndexWithTheSameRankRating(array $battleFieldCards, array $playerCardIndexes): ?int
    {

            foreach ($playerCardIndexes AS $playerCardIndex)
            {
                foreach ($battleFieldCards AS $battleFieldCard)
                {

                    if($this->cards[$playerCardIndex]->getCardRating() == $battleFieldCard->getCardRating())
                    {
                        return $playerCardIndex;
                    }
            }
        }

        return null;
    }


    /**
     * @param Card[] $battleFieldCards
     * @param Card[] $playerCards
     * @return int|null
     */
    private function findCardIndexWithTheSameRank(array $battleFieldCards, array $playerCardIndexes): ?int
    {

        foreach ($playerCardIndexes AS $playerCardIndex)
        {
            foreach ($battleFieldCards AS $battleFieldCard)
            {
                if($this->cards[$playerCardIndex]->getRank()->getValue() == $battleFieldCard->getRank()->getValue())
                {
                    return $playerCardIndex;
                }
            }
        }

        return null;
    }



    private function findFirstCardToFight(): ?Card
    {
        $minTrumpCardIndex = $this->getMinTrumpIndex();
        $minSimpleCardIndex = $this->getMinSimpleCardIndex();

        if(!is_null($minSimpleCardIndex))
        {
            $card = $this->cards[$minSimpleCardIndex];
            unset($this->cards[$minSimpleCardIndex]);
            return $card;
        } elseif(!is_null($minTrumpCardIndex))
        {
            $card = $this->cards[$minTrumpCardIndex];
            unset($this->cards[$minTrumpCardIndex]);
            return $card;
        }

        return null;
    }

    private function getMinSimpleCardIndex()
    {
        $result = null;
        foreach ($this->cards AS $index => $card)
        {
            if(!$card->isTrump())
            {
                if(is_null($result))
                {
                    $result = $index;
                }
                else
                {
                    if($this->cards[$index]->getCardRating() < $this->cards[$result]->getCardRating())
                    {
                        $result = $index;
                    }
                }
            }
        }

        return $result;
    }

    private function getMinTrumpIndex(): ?int
    {
        $result = null;
        foreach ($this->cards AS $index => $card)
        {
            if($card->isTrump())
            {
                return $index;
            }
        }

        return $result;
    }

    private function getMaxTrumpIndex(): ?int
    {
        $result = null;
        foreach ($this->cards AS $index => $card)
        {
            if($card->isTrump())
            {
                $result = $index;
            }
        }

        return $result;
    }

    /**
     * Отбиваться необходимо старшей картой той же масти.
     * Выбирается самая младшая из возможных карт для отбивания.
     * Если нет подходящих карт той же масти, то отбиваться нужно самой младшей козырной картой;
     * @param Card $fightCard
     * @return Card
     * @throws PlayerCardsException
     */
    public function findCardToBeat(Card $fightCard): ?Card
    {
        if(count($this->cards) == 0)
        {
            return null;
        }
        $minTrumpIndex = $this->getMinTrumpIndex();

        if($fightCard->isTrump() && is_null($minTrumpIndex))
        {
            return null;
        }

        foreach ($this->cards AS $index => $card)
        {
            if ($card->getSuitName() == $fightCard->getSuitName() && $card->getCardRating() > $fightCard->getCardRating())
            {
                unset($this->cards[$index]);
                return $card;
            }
        }

        //Не нашли подходящих карт той же масти, отбиваемся нужно самой младшей козырной картой;
        if(is_null($minTrumpIndex))
        {
            return null;
        }

        foreach ($this->cards as $card)
        {
            if ($card->getCardRating() > $fightCard->getCardRating())
            {
                $cardToBeat = $this->cards[$minTrumpIndex];
                unset($this->cards[$minTrumpIndex]);
                return $cardToBeat;
            }
        }



        return null;
    }

    public function hasCards(): bool
    {
        return count($this->cards) > 0;
    }

    /**
     * @param Card $card
     * @return Card[]
     */
    private function extractSimilarCardsByRankRating(Card $cardToCompareWith): array
    {
        $result = [];
        foreach ($this->cards as $index => $card)
        {
            if( $card->getRank()->getValue() == $cardToCompareWith->getRank()->getValue())
            {
                if($this->getMaxTrumpIndex() != $index)
                {
                    $result[] = $card;
                    unset($this->cards[$index]);
                }
            }
        }

        return $result;
    }

    /**
     * Метод извлечет все карты у игрока, одинаковые по достоинству, кроме козырей.
     * @param Card[] $cardsToCompareWith
     * @return Card[]
     */
    public function extractCardsWithSimilarRankRatingButNotTrumps(array $cardsToCompareWith): array
    {
        $result = [];
        foreach ($cardsToCompareWith as $cardToCompareWith) {
            $result = array_merge($result, $this->extractSimilarCardsByRankRating($cardToCompareWith));
        }
        return $result;
    }

}