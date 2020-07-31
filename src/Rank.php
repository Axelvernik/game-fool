<?php


namespace kymbrik;


class Rank extends BasicEnum
{
    const ACE = 'Т';
    const KING = 'К';
    const QUEEN = 'Д';
    const JACK = 'В';
    const CARD10 = '10';
    const CARD9 = '9';
    const CARD8 = '8';
    const CARD7 = '7';
    const CARD6 = '6';

    const RANKS_RATING  = [self::CARD6 => 1,
        self::CARD7 => 2,
        self::CARD8 => 3,
        self::CARD9 => 4,
        self::CARD10 => 5,
        self::JACK => 6,
        self::QUEEN => 7,
        self::KING => 8,
        self::ACE => 9
    ];

    /**
     * @var string $value
     */
    private $value;

    /**
     * @var $rating int
     */
    private $rating;

    /**
     * Rank constructor.
     * @param string $value
     */
    public function __construct(string $value)
    {
        if($this->isRankExist($value))
        {
            $this->value = $value;
        }
        $this->rating = self::RANKS_RATING[$this->value];
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public function getRankRating(bool $isTrump)
    {
        return $isTrump ? $this->rating * 10 : $this->rating;
    }

    public  static function getSortedRankNames()
    {
        $ranksRatingFlipped = array_flip(self::RANKS_RATING);
        ksort($ranksRatingFlipped);
        return $ranksRatingFlipped;
    }

    public function isRankExist(string $value): bool{
        if(!array_key_exists($value, self::RANKS_RATING))
        {
            throw new RankException("rating for this card rank does not exist.");
        }

        return true;
    }


}