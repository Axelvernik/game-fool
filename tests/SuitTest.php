<?php declare(strict_types=1);

use kymbrik\CardsDeck;
use kymbrik\GameFool;
use kymbrik\GameFoolException;
use kymbrik\Player;
use kymbrik\Suit;
use PHPUnit\Framework\TestCase;


final class SuitTest extends TestCase
{
    public function testGetSuits(): void
    {
        $suits = Suit::getSuits();

        var_dump( $suits);
    }


}
