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
}
