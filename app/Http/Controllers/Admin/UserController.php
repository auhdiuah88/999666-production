<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function findCustomerServiceByPhone(Request $request){
        try{
            $validator = Validator::make($request->input(),[
                'phone' => 'required|regex:/^\d{8,13}$/'
            ]);
            if($validator->fails()){
                return $this->AppReturn(402, $validator->errors()->first());
            }
            $this->UserService->findCustomerServiceByPhone($request->get('phone'));
            return $this->AppReturn(
                $this->UserService->_code,
                $this->UserService->_msg,
                $this->UserService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminErr', $e);
            return $this->AppReturn(402, $e->getMessage());
        }
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
        try{
            $this->UserService->editUser($request->post());
            return $this->AppReturn(
                $this->UserService->_code,
                $this->UserService->_msg,
                $this->UserService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminErr', $e);
            return $this->AppReturn(402, $e->getMessage());
        }
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
        $this->UserService->giftMoney($request->post("id"), $request->post("money"), $request->header("token"));
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }

    public function upperSeparation(Request $request)
    {
        $this->UserService->upperSeparation($request->post("id"), $request->post("money"), $request->header("token"));
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }

    public function downSeparation(Request $request)
    {
        $this->UserService->downSeparation($request->post("id"), $request->post("money"), $request->header("token"));
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }

    public function getBalanceLogs(Request $request)
    {
        $this->UserService->getBalanceLogs($request->post("id"), $request->post("page"), $request->post("limit"));
        return $this->AppReturn(
            $this->UserService->_code,
            $this->UserService->_msg,
            $this->UserService->_data
        );
    }

    public function editFakeBettingMoney(Request $request){
        try{
            $validator = Validator::make($request->input(),[
                'money' => 'required|integer',
                'user_id' => 'required|integer|gt:0'
            ]);
            if($validator->fails()){
                return $this->AppReturn(402, $validator->errors()->first());
            }
            $this->UserService->editFakeBettingMoney();
            return $this->AppReturn(
                $this->UserService->_code,
                $this->UserService->_msg,
                $this->UserService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminErr', $e);
            return $this->AppReturn(402, $e->getMessage());
        }
    }

    public function clearFakeBetting()
    {
        try{
            $this->UserService->clearFakeBetting();
            return $this->AppReturn(
                $this->UserService->_code,
                $this->UserService->_msg,
                $this->UserService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminErr', $e);
            return $this->AppReturn(402, $e->getMessage());
        }
    }

    public function searchUserByPhoneLike()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'phone' => ['required', 'min:5']
            ]);
            if($validator->fails())
                return $this->AppReturn(402, $validator->errors()->first());
            $this->UserService->searchUserByPhoneLike();
            return $this->AppReturn(
                $this->UserService->_code,
                $this->UserService->_msg,
                $this->UserService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminErr', $e);
            return $this->AppReturn(402, $e->getMessage());
        }
    }

}
