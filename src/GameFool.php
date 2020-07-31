<?php

namespace kymbrik;

class GameFool
{
    private const MIN_PLAYERS_CNT = 2;
    private const MAX_PLAYERS_CNT = 4;

    /**
     * Индекс нападающего игрока
     * @var int
     */
    private $attackingPlayerIndex = 0;

    /**
     * Индекс обороняющегося игрока
     * @var int
     */
    private $beatingPlayerIndex = 1;

    /**
     * @var $players Player[]
     */
    private $players;

    /**
     * @var $cardsDeck CardsDeck
     */
    private $cardsDeck;

    /**
     * @var int
     */
    private $playersCnt;

    private function getActivePlayersCnt(): int
    {
        $result = 0;
        foreach ($this->players AS $player)
        {
            if(!$player->isOutOfTheGame())
            {
                $result++;
            }
        }

        return $result;
    }

    private function getPlayersCnt()
    {
        return $this->playersCnt;
    }

    private function isPlayersInInterval(): bool
    {
        $this->playersCnt = count($this->players);
        return $this->playersCnt <= self::MAX_PLAYERS_CNT && $this->playersCnt >= self::MIN_PLAYERS_CNT;
    }

    private function start()
    {
        if(!$this->isPlayersInInterval())
        {
            throw new GameFoolException("Too many or too litle players");
        }

        //Перед началом игры колода сортируется заданным случайным числом
        $this->cardsDeck->sort();
        //Раздаем карты
        $this->dealCards();
        //Создаем козырную масть
        $this->cardsDeck->createTrump();
        //Сортируем карты игроков
        $this->sortCardsOfPlayers();

        //echo PHP_EOL . "Козырь: {$this->cardsDeck->getTrumpCard()->getSuitName()} {$this->cardsDeck->getTrumpCard()->getRank()->getValue()}" . PHP_EOL;

        $index = 0;
        do
        {
           // echo "Карт в колоде: {$this->cardsDeck->getDeckCount()}" . PHP_EOL;
           // echo "Сделали ход {$index}" . PHP_EOL;
            $index++;

        } while($this->move());
          //  echo "Игра завершена." . PHP_EOL;
    }

    private function getFoolPlayerName(): ?string
    {

        foreach ($this->players AS $player)
        {
            if(!$player->isOutOfTheGame())
            {
               return $player->getName();
            }
        }

        return null;

    }


    private function move(): bool
    {
        $attackingPlayer = $this->players[$this->attackingPlayerIndex];
        $beatingPlayer = $this->players[$this->beatingPlayerIndex];
        $fightHandler = new FightHandler($attackingPlayer, $beatingPlayer, $this->cardsDeck);

      //  $attackingPlayer->printCards();
       // echo " vs ";
       // $beatingPlayer->printCards();

        $fightHandler->handle();

        //После каждого хода игроки берут недостающие количество карт из начала массива колоды.
        //Вначале пополняет руку картами нападающий, затем обороняющийся и затем все остальные по порядку их следования;

        if($this->isEnd()) {
            return false;
        }
        try
        {
            $this->prepareNextMove();
        } catch (GameFoolException $ex)
        {
            return false;
        }

        return true;
    }

    private function isEnd(): bool
    {
        return !$this->cardsDeck->isNotEmpty() && $this->getActivePlayersCnt() <= 1;
    }

    private function findNextBeatingPlayerIndex(int $attackingPlayerNextIndex): int
    {
        // Определяем сначала те, которые идут после нападающего игрока
        for($i = $attackingPlayerNextIndex + 1; $i < $this->getPlayersCnt(); $i++)
        {
            if(!$this->players[$i]->isOutOfTheGame())
            {
                return $i;
            }
        }

        //Не нашли после обороняющегося, ищем до
        for($i = 0; $i < $attackingPlayerNextIndex; $i++)
        {
            if(!$this->players[$i]->isOutOfTheGame())
            {
                return $i;
            }
        }

        throw new GameFoolException("Game over. No active users. {$this->players[$attackingPlayerNextIndex]->getName()} is loser");
    }

    private function findNextAttackingPlayerIndex(): int
    {
        $beatingPlayer = $this->players[$this->beatingPlayerIndex];
        if(!$beatingPlayer->isOutOfTheGame() && !$beatingPlayer->isDefeated())
        {
            return $this->beatingPlayerIndex;
        }

        // Определяем сначала те, которые идут после обороняющегося игрока
        for($i = $this->beatingPlayerIndex + 1; $i < $this->getPlayersCnt(); $i++)
        {
            if(!$this->players[$i]->isOutOfTheGame())
            {
                return $i;
            }
        }

        //Не нашли после обороняющегося, ищем до
        for($i = 0; $i < $this->beatingPlayerIndex; $i++)
        {
            if(!$this->players[$i]->isOutOfTheGame())
            {
                return $i;
            }
        }

        throw new GameFoolException("Game over. No active users. {$beatingPlayer->getName()} is loser");
    }

    private function resetDefeatStatusForBeatedPlayer()
    {
        $this->players[$this->beatingPlayerIndex]->setDefeated(false);
    }

    private function prepareNextMove()
    {
        $this->resetPlayersActivity();
        $attackingPlayerNextIndex = $this->findNextAttackingPlayerIndex();
        $beatingPlayerNextIndex = $this->findNextBeatingPlayerIndex($attackingPlayerNextIndex);
        $this->dealCards();

        $this->resetDefeatStatusForBeatedPlayer();

        $this->attackingPlayerIndex = $attackingPlayerNextIndex;
        $this->beatingPlayerIndex = $beatingPlayerNextIndex;

        $this->sortCardsOfPlayers();
    }

    /**
     * Если в руках игрока не осталось карт и в колоде пусто, то этот игрок выходит из игры;
     */
    private function resetPlayersActivity()
    {
        foreach ($this->players AS $player)
        {
            if(!$player->hasCards() && !$this->cardsDeck->isNotEmpty())
            {
                $player->setIsOutOfTheGame(true);
            }
        }
    }

    /**
     * На протяжении всей игры карты в руках игроков должны быть сортированы.
     * Вначале идут карты всех не козырных мастей, сортированные по достоинству и по масти
     * (порядок: пика, крест, бубен, червей), затем козыри,
     * также сортированные по достоинству;
     */
    public function sortCardsOfPlayers()
    {
        foreach ($this->players as $player)
        {
            $player->sortCards($this->cardsDeck->getTrumpSuitName());
        }
    }

    /**
     *     После сортировки колоды выполняется 6 итераций раздачи карт из начала массива колоды:
     *     для каждого игрока, в порядке их добавления в игру, достается карта из начала колоды (начало массива).
     *     Таким образом после 6 итераций у каждого игрока должно быть 6 карт в руках;
     *
     *      После каждого хода игроки берут недостающие количество карт из начала массива колоды.
     *      Вначале пополняет руку картами нападающий, затем обороняющийся и затем все остальные по порядку их следования;
     */
    public function dealCards()
    {

            for($j = $this->attackingPlayerIndex; $j < $this->getPlayersCnt(); $j++) {
                for($i = 0; $i < 6; $i++) {
                    $this->handleDealCard($this->players[$j]);
                }
            }
            for($k = 0; $k < $this->attackingPlayerIndex; $k++) {
                for($i = 0; $i < 6; $i++) {
                    $this->handleDealCard($this->players[$k]);
                }
            }

    }

    private function handleDealCard(Player $player)
    {
        if(!$player->isOutOfTheGame() && !$player->hasEnoughCards() && $this->cardsDeck->isNotEmpty()) {
            $player->takeCard($this->cardsDeck->extractCard());
        }
    }

    public function __invoke(object $object = null)
    {
        if ($object instanceof Player) {
            $this->players[] = $object;
        }
        if ($object instanceof CardsDeck) {
            $this->cardsDeck = $object;
        }
        if ($object == null) {
            $this->start();
            if ($this->getActivePlayersCnt() == 0)
            {
                return "-";
            }
            else
            {
                return $this->getFoolPlayerName();
            }
        }

        return $this;
    }

}