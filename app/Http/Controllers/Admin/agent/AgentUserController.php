<?php


namespace App\Http\Controllers\Admin\agent;


use App\Http\Controllers\Controller;
use App\Services\Admin\agent\AgentUserService;
use App\Services\Admin\UserService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AgentUserController extends Controller
{

    private $AgentUserService, $UserService;

    public function __construct
    (
        AgentUserService $agentUserService,
        UserService $userService
    )
    {
        $this->AgentUserService = $agentUserService;
        $this->UserService = $userService;
    }

    public function index(){
        try{
            $this->AgentUserService->searchUser();
            return $this->AppReturn(
                $this->AgentUserService->_code,
                $this->AgentUserService->_msg,
                $this->AgentUserService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function firstRechargeList(){
        try{
            $this->AgentUserService->firstRechargeList();
            return $this->AppReturn(
                $this->AgentUserService->_code,
                $this->AgentUserService->_msg,
                $this->AgentUserService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function orderInfo(){
        try{
            $this->AgentUserService->orderInfoList();
            return $this->AppReturn(
                $this->AgentUserService->_code,
                $this->AgentUserService->_msg,
                $this->AgentUserService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function editUser()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'id' => ['required', 'integer', 'gte:1'],
                'fake_betting_money' => ['gte:0', 'numeric'],
                'is_login' => ['required', Rule::in(0,1)],
                'is_transaction' => ['required', Rule::in(0,1)],
                'is_recharge' => ['required', Rule::in(0,1)],
                'is_withdrawal' => ['required', Rule::in(0,1)],
                'is_betting_notice' => ['required', Rule::in(0,1)],
                'remarks' => ['max:200']
            ]);
            if($validator->fails())
                return $this->AppReturn(402,$validator->errors()->first());
            $this->UserService->editUser(request()->post());
            return $this->AppReturn(
                $this->AgentUserService->_code,
                $this->AgentUserService->_msg,
                $this->AgentUserService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

}
