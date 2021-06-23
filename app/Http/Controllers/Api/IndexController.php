<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Services\Api\IndexService;

class IndexController extends Controller
{

    protected $IndexService;

    public function __construct
    (
        IndexService $indexService
    )
    {
        $this->IndexService = $indexService;
    }

    public function tips()
    {
        $this->IndexService->tips();
        return $this->AppReturn(
            $this->IndexService->_code,
            $this->IndexService->_msg,
            $this->IndexService->_data
        );
    }

    public function gameCateList()
    {
        $this->IndexService->gameCateList();
        return $this->AppReturn(
            $this->IndexService->_code,
            $this->IndexService->_msg,
            $this->IndexService->_data
        );
    }

    public function cateDetail()
    {
        $this->IndexService->cateDetail();
        return $this->AppReturn(
            $this->IndexService->_code,
            $this->IndexService->_msg,
            $this->IndexService->_data
        );
    }

    public function rgRecord()
    {
        $this->IndexService->rgRecord();
        return $this->AppReturn(
            $this->IndexService->_code,
            $this->IndexService->_msg,
            $this->IndexService->_data
        );
    }

    public function adsDetail()
    {
        $this->IndexService->adsDetail();
        return $this->AppReturn(
            $this->IndexService->_code,
            $this->IndexService->_msg,
            $this->IndexService->_data
        );
    }

}
