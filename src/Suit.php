<?php


namespace kymbrik;


class Suit extends BasicEnum
{

    const SPADE = '♠'; //♠
    const HEART = '♥'; //♥
    const CLUB = '♣'; //♣
    const DIAMOND = '♦'; //♦

    /**
     * @var $isTrump bool
     */
    private $isTrump;

    /**
     * @var $suitName string
     */
    private $suitName;

    /**
     * Suit constructor.
     * @param bool $isTrump
     * @param string $suitName
     */
    public function __construct( string $suitName, bool $isTrump = false)
    {
        if ($this->isSuitExist($suitName))
        {
            $this->suitName = $suitName;
        }
        $this->isTrump = $isTrump;
    }

    private function isSuitExist(string $suitName): bool
    {
        if(!array_key_exists($suitName, self::getSuits()))
        {
            throw new RankException("{$suitName} suit does not exist.");
        }

        return true;
    }

    public static function getSuits(): array
    {
        return array_flip(self::getConstants());
    }

    /**
     * @return bool
     */
    public function isTrump(): bool
    {
        return $this->isTrump;
    }

    /**
     * @param bool $isTrump
     */
    public function setIsTrump(bool $isTrump): void
    {
        $this->isTrump = $isTrump;
    }

    /**
     * @return string
     */
    public function getSuitName(): string
    {
        return $this->suitName;
    }

}