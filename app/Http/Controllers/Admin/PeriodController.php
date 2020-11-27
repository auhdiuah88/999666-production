<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\PeriodService;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    private $PeriodService;

    public function __construct(PeriodService $periodService)
    {
        $this->PeriodService = $periodService;
    }

    public function findAll(Request $request)
    {
        $this->PeriodService->findAll($request->get("page"), $request->get("limit"), $request->get("status"));
        return $this->AppReturn(
            $this->PeriodService->_code,
            $this->PeriodService->_msg,
            $this->PeriodService->_data
        );
    }

    public function searchPeriod(Request $request)
    {
        $this->PeriodService->searchPeriod($request->post());
        return $this->AppReturn(
            $this->PeriodService->_code,
            $this->PeriodService->_msg,
            $this->PeriodService->_data
        );
    }

    public function findById(Request $request)
    {
        $this->PeriodService->findById($request->get("id"));
        return $this->AppReturn(
            $this->PeriodService->_code,
            $this->PeriodService->_msg,
            $this->PeriodService->_data
        );
    }
}
