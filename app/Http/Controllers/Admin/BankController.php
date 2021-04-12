<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\BankService;
use Illuminate\Http\Request;

class BankController extends Controller
{
    private $BankService;

    public function __construct(BankService $bankService)
    {
        $this->BankService = $bankService;
    }

    public function findAll(Request $request)
    {
        $this->BankService->findAll($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->BankService->_code,
            $this->BankService->_msg,
            $this->BankService->_data
        );
    }

    public function findById(Request $request)
    {
        $this->BankService->findById($request->get("id"));
        return $this->AppReturn(
            $this->BankService->_code,
            $this->BankService->_msg,
            $this->BankService->_data
        );
    }

    public function addBank(Request $request)
    {
        $this->BankService->addBank($request->post());
        return $this->AppReturn(
            $this->BankService->_code,
            $this->BankService->_msg,
            $this->BankService->_data
        );
    }

    public function editBank(Request $request)
    {
        $this->BankService->editBank($request->post());
        return $this->AppReturn(
            $this->BankService->_code,
            $this->BankService->_msg,
            $this->BankService->_data
        );
    }

    public function delBank(Request $request)
    {
        $this->BankService->delBank($request->post("id"));
        return $this->AppReturn(
            $this->BankService->_code,
            $this->BankService->_msg,
            $this->BankService->_data
        );
    }

    public function searchBank(Request $request)
    {
        $this->BankService->searchBank(
            $request->post("phone"),
            $request->post("account_holder"),
            $request->post("page"),
            $request->post("limit")
        );
        return $this->AppReturn(
            $this->BankService->_code,
            $this->BankService->_msg,
            $this->BankService->_data
        );
    }
}
