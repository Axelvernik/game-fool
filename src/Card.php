<?php


namespace kymbrik;


class Card
{
    /**
     * @var $suit Suit
     */
    private $suit;

    /**
     * @var $rank Rank
     */
    private $rank;

    /**
     * Card constructor.
     * @param Suit $suit
     * @param Rank $rank
     */
    public function __construct(Suit $suit, Rank $rank)
    {
        $this->suit = $suit;
        $this->rank = $rank;
    }


    public function getCardRating(): int
    {
        return $this->getRank()->getRankRating($this->suit->isTrump());
    }

    public function setAsTrump()
    {
        $this->suit->setIsTrump(true);
    }

    public function getSuitName(): string
    {
        return $this->suit->getSuitName();
    }

    public function isTrump()
    {
        return $this->suit->isTrump();
    }

    /**
     * @return Suit
     */
    public function getSuit(): Suit
    {
        return $this->suit;
    }

    /**
     * @param Suit $suit
     */
    public function setSuit(Suit $suit): void
    {
        $this->suit = $suit;
    }

    /**
     * @return Rank
     */
    public function getRank(): Rank
    {
        return $this->rank;
    }

    /**
     * @param Rank $rank
     */
    public function setRank(Rank $rank): void
    {
        $this->rank = $rank;
    }

}