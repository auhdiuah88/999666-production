<?php


namespace App\Libs\Games;

class GameContext
{

    private $GameList = [];

    public function __construct
    (
        \App\Libs\Games\WDYY\Client $wdyy
    )
    {
        $this->GameList = [
            'wdyy' => $wdyy
        ];
    }

    public function getStrategy(string $strategy)
    {
        if (!isset($this->GameList[$strategy])) {
            return false;
        }
        return $this->GameList[$strategy];
    }

}
