<?php
namespace kymbrik;

class CardsDeck
{

    /**
     * @var $cards Card[]
     */
    private $cards = [];

    /**
     * @var $randNumber int
     */
    private $randNumber;

    /**
     * @var $trumpCard Card
     */
    private $trumpCard;

    /**
     * @var $suits Suit[]
     */
    private $suits;

    /**
     * CardsDeck constructor.
     * @param int $randNumber
     */
    public function __construct(int $randNumber)
    {
        $this->randNumber = $randNumber;
        $this->createSuits();
        //♠, ♥, ♣, ♦
        $this->generateCards();
    }

    private function createSuits()
    {
        $this->suits = [
            new Suit(Suit::SPADE),
            new Suit(Suit::HEART),
            new Suit(Suit::CLUB),
            new Suit(Suit::DIAMOND)
        ];
    }

    /**
     * Изначально колода отсортирована по масти в следующем порядке ♠, ♥, ♣, ♦.
     */
    private function generateCards()
    {
        foreach ($this->suits as $suit)
        {
            $cardsBySuit = $this->generateCardsBySuit($suit);
            $this->cards = array_merge($this->cards, $cardsBySuit);
        }
    }

    public function isNotEmpty(): bool
    {
        return count($this->cards) > 0;
    }

    public function getDeckCount(): int
    {
        return count($this->cards);
    }

    private function generateCardsBySuit(Suit $suit): array
    {
        $result = [];
        $ranks = Rank::getSortedRankNames();
        foreach ($ranks as $rank)
        {
            $result[] = new Card($suit, new Rank($rank));
        }
        return $result;
    }

    /**
     *   Перед началом игры колода сортируется заданным случайным числом: выполняется 1000 итераций,
     *   в каждой итерации из колоды берется карта с порядковым номер n = (random + iterator * 2) mod 36
     *   (где random - случайное переданное число, а iterator - номер итерации сортировки 0…999) и
     *   перемещается в начало колоды;
     */
    public function sort()
    {
        for($i = 0; $i < 1000; $i++)
        {
            $n = ($this->randNumber + ($i * 2)) % 36;
            $cardToStart = $this->cards[$n];
            unset($this->cards[$n]);
            $this->cards = array_merge([$cardToStart],$this->cards );
        }
    }

    public function extractCard(): Card
    {
        if($this->getDeckCount() == 0)
        {
            throw new CardsDeckExceptions("No cards in deck left.");
        }
        return array_shift($this->cards);
    }

    public function createTrump()
    {
        $this->trumpCard = array_shift($this->cards);
        $this->trumpCard->setAsTrump();
        $this->cards[] = $this->trumpCard;
    }

    /**
     * @return Card
     */
    public function getTrumpCard(): Card
    {
        return $this->trumpCard;
    }

    public function getTrumpSuitName(): string
    {
        return $this->trumpCard->getSuitName();
    }



}