<?php


namespace kymbrik;


use SebastianBergmann\CodeCoverage\Report\PHP;

class FightHandler
{
    /**
     * @var Player
     */
    private $attackingPlayer;
    /**
     * @var Player
     */
    private $beatingPlayer;

    /**
     * @var array
     */
    private $attackingPlayerCards = [];

    /**
     * @var array
     */
    private $beatingPlayerCards = [];

    /**
     * @var $fightEnded bool
     */
    private $fightEnded;
    /**
     * @var CardsDeck
     */
    private CardsDeck $cardsDeck;


    /**
     * FightHandler constructor.
     * @param Player $attackingPlayer
     * @param Player $beatingPlayer
     * @param CardsDeck $cardsDeck
     */
    public function __construct(Player $attackingPlayer, Player $beatingPlayer, CardsDeck $cardsDeck)
    {
        $this->attackingPlayer = $attackingPlayer;
        $this->beatingPlayer = $beatingPlayer;
        $this->cardsDeck = $cardsDeck;
    }

    private function saveAttackingCard(Card $card): void
    {
        $this->attackingPlayerCards[] = $card;
    }

    private function saveBeatingCard(Card $card): void
    {
        $this->beatingPlayerCards[] = $card;
    }

    public function handle()
    {



        if($this->attackingPlayer->hasCards())
        {

            $fightCard = $this->attackingPlayer->findCardToFight($this->getAllBattleFieldCards());

            if(!is_null($fightCard)) {
              //  echo PHP_EOL . "{$this->attackingPlayer->getName()} --> {$fightCard->getRank()->getValue()}{$fightCard->getSuitName()}" . PHP_EOL;
                $this->saveAttackingCard($fightCard);

                $beatingCard = $this->beatOffCard($fightCard);
                if (is_null($beatingCard)) {
                //    echo "{$this->beatingPlayer->getName()} <-- {$fightCard->getRank()->getValue()}{$fightCard->getSuitName()}" . PHP_EOL;
                //    echo "Бой закончен, у отбивающегося нечем отбиться". PHP_EOL . PHP_EOL;
                    $this->beatingPlayer->setDefeated(true);

                    //Довыкладываем карты на стол атакующего, даже если козырь
                    while (!is_null($fightCard = $this->attackingPlayer->findCardToFight($this->getAllBattleFieldCards())))
                    {
                        $this->saveAttackingCard($fightCard);
                    }



                } else {
                 //   echo "{$beatingCard->getRank()->getValue()}{$beatingCard->getSuitName()} <-- {$this->beatingPlayer->getName()}" . PHP_EOL;
                    $this->saveBeatingCard($beatingCard);
                    //Ведем бой дальше, не факт, что еще нет карт у игроков.
                    $this->handle();
                }


            }
        }

        $this->distributeCards();
    }

    /**
            Если отбиваться нечем больше, то отбивающий забирает все карты, что были использованы за ход.
            Также, у нападающего забираются все карты такого же достоинства, что были использованы за ход, кроме козырей.
            Иными словами, если козырь черва, за ход использовалась карта 10 пика и на в руках нападающего есть ещё 10 чева
            и 10 бубен, то 10 бубен переходит в руки отбивающегося.
     */
    private function distributeCards()
    {
        //Не распределяем карты, если отбивающийся защитился успешно

        if($this->beatingPlayer->isDefeated())
        {
            // Передаем все карты, которые были в ходу отбивающемуся
            foreach ($this->beatingPlayerCards as $beatingPlayerCard)
            {
                $this->beatingPlayer->takeCard($beatingPlayerCard);
            }
            foreach ($this->attackingPlayerCards as $attackingPlayerCard)
            {
                $this->beatingPlayer->takeCard($attackingPlayerCard);
            }

            //Также, у нападающего забираются все карты такого же достоинства, что были использованы за ход, кроме козырей.
            $this->transferSimilarByRankRatingCards();
        }
    }

    public function transferSimilarByRankRatingCards()
    {
        $cardsToTransfer = $this->attackingPlayer->extractCardsWithSimilarRankRatingButNotTrumps(
            $this->getAllBattleFieldCards()
        );

        foreach ($cardsToTransfer AS $cardToTransfer)
        {
            $this->beatingPlayer->takeCard($cardToTransfer);
        }

    }

    /**
     * Отбить карту.
     *
     * @param Card $fightCard
     * @return Card|null if player cant beat off card
     * @throws PlayerCardsException
     */
    private function beatOffCard(Card $fightCard): ?Card
    {
        return $this->beatingPlayer->findCardToBeat($fightCard);
    }

    /**
     * @return Card[]
     */
    private function getAllBattleFieldCards(): array
    {
        return CardSorter::sort($this->cardsDeck->getTrumpSuitName(), array_merge($this->attackingPlayerCards, $this->beatingPlayerCards));
    }

}