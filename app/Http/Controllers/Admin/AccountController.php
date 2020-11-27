<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\AccountService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    private $AccountService;

    public function __construct(AccountService $accountService)
    {
        $this->AccountService = $accountService;
    }

    public function findAll(Request $request)
    {
        $this->AccountService->findAll($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->AccountService->_code,
            $this->AccountService->_msg,
            $this->AccountService->_data
        );
    }

    public function findById(Request $request)
    {
        $this->AccountService->findById($request->get("id"));
        return $this->AppReturn(
            $this->AccountService->_code,
            $this->AccountService->_msg,
            $this->AccountService->_data
        );
    }

    public function addAccount(Request $request)
    {
        $this->AccountService->addAccount($request->post());
        return $this->AppReturn(
            $this->AccountService->_code,
            $this->AccountService->_msg,
            $this->AccountService->_data
        );
    }

    public function editAccount(Request $request)
    {
        $this->AccountService->editAccount($request->post());
        return $this->AppReturn(
            $this->AccountService->_code,
            $this->AccountService->_msg,
            $this->AccountService->_data
        );
    }

    public function delAccount(Request $request)
    {
        $this->AccountService->delAccount($request->post("id"));
        return $this->AppReturn(
            $this->AccountService->_code,
            $this->AccountService->_msg,
            $this->AccountService->_data
        );
    }

    public function searchAccount(Request $request)
    {
        $this->AccountService->searchAccount($request->post());
        return $this->AppReturn(
            $this->AccountService->_code,
            $this->AccountService->_msg,
            $this->AccountService->_data
        );
    }
}
