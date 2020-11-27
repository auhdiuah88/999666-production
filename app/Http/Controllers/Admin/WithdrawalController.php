<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\WithdrawalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class WithdrawalController extends Controller
{
    private $WithdrawalService;

    public function __construct(WithdrawalService $withdrawalService)
    {
        $this->WithdrawalService = $withdrawalService;
    }

    public function findAll(Request $request)
    {
        $this->WithdrawalService->findAll($request->get("page"), $request->get("limit"), $request->get("status"));
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }

    public function auditRecord(Request $request)
    {
        $this->WithdrawalService->auditRecord($request->post());
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }

    public function batchPassRecord(Request $request)
    {
        $this->WithdrawalService->batchPassRecord($request->post());
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }

    public function batchFailureRecord(Request $request)
    {
        $this->WithdrawalService->batchFailureRecord($request->post());
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }

    public function searchRecord(Request $request)
    {
        $this->WithdrawalService->searchRecord($request->post());
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }
}
