<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\ChargeService;
use Illuminate\Http\Request;

class ChargeController extends Controller
{
    private $ChargeService;

    public function __construct(ChargeService $chargeService)
    {
        $this->ChargeService = $chargeService;
    }

    public function findAll(Request $request)
    {
        $this->ChargeService->findAll($request->get("page"), $request->get("limit"), $request->get("status"));
        return $this->AppReturn(
            $this->ChargeService->_code,
            $this->ChargeService->_msg,
            $this->ChargeService->_data
        );
    }

    public function searchChargeLogs(Request $request)
    {
        $this->ChargeService->searchChargeLogs($request->post());
        return $this->AppReturn(
            $this->ChargeService->_code,
            $this->ChargeService->_msg,
            $this->ChargeService->_data
        );
    }
}
