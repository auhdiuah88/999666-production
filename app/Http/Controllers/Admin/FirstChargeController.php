<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\FirstChargeService;
use Illuminate\Http\Request;

class FirstChargeController extends Controller
{
    private $FirstChargeService;

    public function __construct(FirstChargeService $chargeService)
    {
        $this->FirstChargeService = $chargeService;
    }

    public function findAll(Request $request)
    {
        $this->FirstChargeService->findAll($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->FirstChargeService->_code,
            $this->FirstChargeService->_msg,
            $this->FirstChargeService->_data
        );
    }

    public function searchChargeLogs(Request $request)
    {
        $this->FirstChargeService->searchChargeLogs($request->post());
        return $this->AppReturn(
            $this->FirstChargeService->_code,
            $this->FirstChargeService->_msg,
            $this->FirstChargeService->_data
        );
    }
}
