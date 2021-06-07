<?php


namespace App\Http\Controllers\Ag;


use App\Services\Ag\GameService;

class Game extends Base
{

    protected $GameService;

    public function __construct
    (
        GameService $gameService
    )
    {
        $this->GameService = $gameService;
    }

    public function bettingList()
    {
        $this->GameService->bettingList();
        return view('ag.betting',['idx'=>6, 'data'=>$this->GameService->_data]);
    }

}
