<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\WhiteListService;
use Illuminate\Http\Request;

class WhiteListController extends Controller
{
    private $WhiteListService;

    public function __construct(WhiteListService $listService)
    {
        $this->WhiteListService = $listService;
    }

    public function findAll(Request $request)
    {
        $this->WhiteListService->findAll($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->WhiteListService->_code,
            $this->WhiteListService->_msg,
            $this->WhiteListService->_data
        );
    }

    public function findById(Request $request)
    {
        $this->WhiteListService->findById($request->get("id"));
        return $this->AppReturn(
            $this->WhiteListService->_code,
            $this->WhiteListService->_msg,
            $this->WhiteListService->_data
        );
    }

    public function addIp(Request $request)
    {
        $this->WhiteListService->addIp($request->post());
        return $this->AppReturn(
            $this->WhiteListService->_code,
            $this->WhiteListService->_msg,
            $this->WhiteListService->_data
        );
    }

    public function editIp(Request $request)
    {
        $this->WhiteListService->editIp($request->post());
        return $this->AppReturn(
            $this->WhiteListService->_code,
            $this->WhiteListService->_msg,
            $this->WhiteListService->_data
        );
    }

    public function delIp(Request $request)
    {
        $this->WhiteListService->delIp($request->post("id"));
        return $this->AppReturn(
            $this->WhiteListService->_code,
            $this->WhiteListService->_msg,
            $this->WhiteListService->_data
        );
    }
}
