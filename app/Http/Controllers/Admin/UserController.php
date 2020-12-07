<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $UserService;

    public function __construct(UserService $userService)
    {
        $this->UserService = $userService;
    }

    public function findAll(Request $request)
    {
        $this->UserService->findAll($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }

    public function findById(Request $request)
    {
        $this->UserService->findById($request->get("id"));
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }

    public function addUser(Request $request)
    {
        $this->UserService->addUser($request->post());
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }

    public function editUser(Request $request)
    {
        $this->UserService->editUser($request->post());
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }

    public function delUser(Request $request)
    {
        $this->UserService->delUser($request->post("id"));
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }

    public function searchUser(Request $request)
    {
        $this->UserService->searchUser($request->post());
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }

    public function batchModifyRemarks(Request $request)
    {
        $this->UserService->batchModifyRemarks($request->post("ids"), $request->post("message"));
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }

    public function modifyUserStatus(Request $request)
    {
        $this->UserService->modifyUserStatus($request->post("id"), $request->post("status"));
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }

    public function getCustomerService()
    {
        $this->UserService->getCustomerService();
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }

    public function modifyCustomerService(Request $request)
    {
        $this->UserService->modifyCustomerService($request->post("ids"), $request->post("customer_id"));
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }

    public function getRecommenders()
    {
        $this->UserService->getRecommenders();
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }

    public function giftMoney(Request $request)
    {
        $this->UserService->giftMoney($request->post("id"), $request->post("money"));
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }
}
