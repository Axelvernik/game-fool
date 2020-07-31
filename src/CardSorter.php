<?php


namespace kymbrik;


class CardSorter
{
    /**
     *
     *
     * Вначале идут карты всех не козырных мастей, сортированные по достоинству и по масти
     * (порядок: пика, крест, бубен, червей), затем козыри,
     * также сортированные по достоинству;
     *
     * @param string $trumpSuitName
     * @param Card[] $cardsToSort
     * @return Card[]
     */
    public static function sort(string $trumpSuitName, array $cardsToSort): array
    {
        $result = [];

        $suitNames = [Suit::SPADE, Suit::CLUB, Suit::DIAMOND, Suit::HEART];

        $cardsSplittedBySuit = [];

        foreach ($cardsToSort as $card) {
            $cardsSplittedBySuit[$card->getSuitName()][$card->getCardRating()] = $card;
        }

        $cardsSplittedBySuitSortedBySuitName = [];
        foreach ($suitNames AS $index => $suitName)
        {
            if(array_key_exists($suitName, $cardsSplittedBySuit))
            {
            $cardsSplittedBySuitSortedBySuitName[$suitName] = $cardsSplittedBySuit[$suitName];
            }
        }


        foreach ($cardsSplittedBySuitSortedBySuitName as $suitName => $cardsBySuit) {
            if(!is_array($cardsBySuit))
            {
                unset($cardsSplittedBySuitSortedBySuitName[$suitName]);
            }
            else
            {
                ksort($cardsSplittedBySuitSortedBySuitName[$suitName]);
            }

        }

        $trumpCards = [];
        foreach ($cardsSplittedBySuitSortedBySuitName as $suitName => $cardsBySuit) {
            if ($trumpSuitName == $suitName) {
                $trumpCards = $cardsBySuit;
            } else {
                $result = array_merge($result, $cardsBySuit);
            }
        }

        //Добавляем козыри в конец
        $result = array_merge($result, $trumpCards);

        return $result;
    }
}