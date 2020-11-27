<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\SpreadService;
use Illuminate\Http\Request;

class SpreadController extends Controller
{
    private $SpreadService;

    public function __construct(SpreadService $spreadService)
    {
        $this->SpreadService = $spreadService;
    }

    public function getProfitList(Request $request)
    {
        $this->SpreadService->getProfitList($request->get("page"), $request->get("limit"), 0);
        return $this->AppReturn(
            $this->SpreadService->_code,
            $this->SpreadService->_msg,
            $this->SpreadService->_data
        );
    }

    public function getLossList(Request $request)
    {
        $this->SpreadService->getProfitList($request->get("page"), $request->get("limit"), 1);
        return $this->AppReturn(
            $this->SpreadService->_code,
            $this->SpreadService->_msg,
            $this->SpreadService->_data
        );
    }
}
