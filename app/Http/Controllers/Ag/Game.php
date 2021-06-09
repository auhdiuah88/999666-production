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

    public function oddsTable()
    {
        return view('ag.adds_table', ['idx'=>4]);
    }

    public function mBettingList()
    {
        $this->GameService->bettingList();
        return view('ag.m.betting',['title'=>trans('ag.betting_manage'), 'data'=>$this->GameService->_data, 'prev'=>1]);
    }

    public function mOddsTable()
    {
        return view('ag.m.adds_table', ['title'=>trans('ag.rate_table'), 'prev'=>1]);
    }

}
