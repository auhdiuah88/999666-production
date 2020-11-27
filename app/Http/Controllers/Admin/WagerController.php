<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\WagerService;
use Illuminate\Http\Request;

class WagerController extends Controller
{
    private $WagerService;

    public function __construct(WagerService $wagerService)
    {
        $this->WagerService = $wagerService;
    }

    public function findAll(Request $request)
    {
        $this->WagerService->findAll($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->WagerService->_code,
            $this->WagerService->_msg,
            $this->WagerService->_data
        );
    }

    public function searchWager(Request $request)
    {
        $this->WagerService->searchWager($request->post());
        return $this->AppReturn(
            $this->WagerService->_code,
            $this->WagerService->_msg,
            $this->WagerService->_data
        );
    }
}
