<?php


namespace App\Libs\Games;

class GameContext
{

    private $GameList = [];

    public function __construct
    (
        \App\Libs\Games\WDYY\Client $wdyy,
        \App\Libs\Games\V8\V8log $v8,
        \App\Libs\Games\ICG\IcgLog $icg
    )
    {
        $this->GameList = [
            'wdyy' => $wdyy,
            "v8" => $v8,
            "icg" => $icg
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
