<?php

namespace App\Http\Controllers;

use App\Repositories\Api\UserRepository;
use App\Services\Api\RechargeService;
use App\Services\PayService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

class TestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function test(RechargeService $rechargeService,UserRepository $userRepository
    ){
//        $user = $userRepository->findByIdUser(15);
//        $this->updateRechargeBalance($user, 20);
       $res =  $userRepository->findAgentByCode('L563KC');
        dd($res['agent']);
    }

//    public function updateRechargeBalance(object $user, $money)
//    {
//        $user->is_first_recharge = (int)$user->is_first_recharge + 1;  // 累计充值次数
//        dd($user->is_first_recharge);
//        $user->total_recharge = bcadd($user->total_recharge, $money, 2);  // 累计充值金额
//
//    }
}
