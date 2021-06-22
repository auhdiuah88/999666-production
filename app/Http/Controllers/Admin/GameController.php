<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\GameService;

class GameController extends Controller
{

    protected $GameService;

    public function __construct
    (
        GameService $gameService
    )
    {
        $this->GameService = $gameService;
    }

    public function cateList()
    {
        $this->GameService->cateList();
        return $this->AppReturn(
            $this->GameService->_code,
            $this->GameService->_msg,
            $this->GameService->_data
        );
    }

    public function parentCateList()
    {
        $this->GameService->parentCateList();
        return $this->AppReturn(
            $this->GameService->_code,
            $this->GameService->_msg,
            $this->GameService->_data
        );
    }

    public function addCate()
    {
        $this->GameService->addCate();
        return $this->AppReturn(
            $this->GameService->_code,
            $this->GameService->_msg,
            $this->GameService->_data
        );
    }

    public function editCate()
    {
        $this->GameService->editCate();
        return $this->AppReturn(
            $this->GameService->_code,
            $this->GameService->_msg,
            $this->GameService->_data
        );
    }

    public function delCate()
    {
        $this->GameService->delCate();
        return $this->AppReturn(
            $this->GameService->_code,
            $this->GameService->_msg,
            $this->GameService->_data
        );
    }

    public function cateDetail()
    {
        $this->GameService->cateDetail();
        return $this->AppReturn(
            $this->GameService->_code,
            $this->GameService->_msg,
            $this->GameService->_data
        );
    }

    public function gameList()
    {
        $this->GameService->gameList();
        return $this->AppReturn(
            $this->GameService->_code,
            $this->GameService->_msg,
            $this->GameService->_data
        );
    }

    public function addGame()
    {
        $this->GameService->addGame();
        return $this->AppReturn(
            $this->GameService->_code,
            $this->GameService->_msg,
            $this->GameService->_data
        );
    }

    public function editGame()
    {
        $this->GameService->editGame();
        return $this->AppReturn(
            $this->GameService->_code,
            $this->GameService->_msg,
            $this->GameService->_data
        );
    }

    public function delGame()
    {
        $this->GameService->delGame();
        return $this->AppReturn(
            $this->GameService->_code,
            $this->GameService->_msg,
            $this->GameService->_data
        );
    }

    public function gameCateList()
    {
        $this->GameService->gameCateList();
        return $this->AppReturn(
            $this->GameService->_code,
            $this->GameService->_msg,
            $this->GameService->_data
        );
    }

}
