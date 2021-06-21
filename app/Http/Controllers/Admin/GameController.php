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

}
