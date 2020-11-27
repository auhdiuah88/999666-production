<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\CommissionService;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    private $CommissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->CommissionService = $commissionService;
    }

    public function findAll(Request $request)
    {
        $this->CommissionService->findAll($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->CommissionService->_code,
            $this->CommissionService->_msg,
            $this->CommissionService->_data
        );
    }

    public function searchCommission(Request $request)
    {
        $this->CommissionService->searchCommission($request->post());
        return $this->AppReturn(
            $this->CommissionService->_code,
            $this->CommissionService->_msg,
            $this->CommissionService->_data
        );
    }
}
