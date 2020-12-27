<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\GroupUserService;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaderController extends UserController
{

    /**
     * @var GroupUserService
     */
    private $groupUserService;

    public function __construct(UserService $userService, GroupUserService $groupUserService)
    {
        parent::__construct($userService);
        $this->groupUserService = $groupUserService;
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'phone' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->AppReturn(400, $validator->errors()->first());
        }
        $this->groupUserService->leaderAdd($request->post());
        return $this->AppReturn(
            $this->groupUserService->_code,
            $this->groupUserService->_msg,
            $this->groupUserService->_data
        );
    }

    public function list(Request $request)
    {
        $this->groupUserService->list($request->all());
        return $this->AppReturn(
            $this->groupUserService->_code,
            $this->groupUserService->_msg,
            $this->groupUserService->_data
        );
    }

    public function logicDel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users',
        ]);
        if ($validator->fails()) {
            return $this->AppReturn(400, $validator->errors()->first());
        }
        $this->groupUserService->logicDel($request->post('id'));
        return $this->AppReturn(
            $this->groupUserService->_code,
            $this->groupUserService->_msg,
            $this->groupUserService->_data
        );
    }

    public function edit(Request $request)
    {
        parent::editUser($request);
    }

    public function searchAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|',
        ]);
        if($validator->fails()){
            return $this->AppReturn(402,$validator->errors()->first());
        }
        $this->groupUserService->searchAccount($request->get('phone'));
        return $this->AppReturn(
            $this->groupUserService->_code,
            $this->groupUserService->_msg,
            $this->groupUserService->_data
        );
    }

    /**
     * 绑定组长
     * @param Request $request
     * @return LeaderController
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function bindAccount(Request $request)
    {
        try{
            $validator = Validator::make($request->post(), [
                'user_id' => 'required|integer|min:1',
                'nickname' => 'required|between:2,20|alpha_dash',
                'account' => "required|unique:admin,username|alpha_num|between:4,20",
                'password' => 'required|between:6,20|alpha_num'
            ]);
            if($validator->fails()){
                return $this->AppReturn(402,$validator->errors()->first());
            }
            $this->groupUserService->bindAccount();
            return $this->AppReturn(
                $this->groupUserService->_code,
                $this->groupUserService->_msg,
                $this->groupUserService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr',$e);
            return $this->AppReturn(402,$e->getMessage());
        }
    }
}
