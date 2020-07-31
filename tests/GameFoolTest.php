<?php declare(strict_types=1);

use kymbrik\CardsDeck;
use kymbrik\GameFool;
use kymbrik\GameFoolException;
use kymbrik\Player;
use PHPUnit\Framework\TestCase;


final class GameFoolTest extends TestCase
{
    public function testInvalidPlayersCount(): void
    {
        $this->expectException(GameFoolException::class);

         (new GameFool())
        (new Player('Rick'))
        (new CardsDeck(rand(1, 0xffff)))
        (new CardsDeck(rand(1, 0xffff)))
        ();
    }


}
