<?php

namespace App\Http\Controllers;

use App\Repositories\Api\UserRepository;
use App\Services\Api\RechargeService;
use App\Services\Api\WithdrawalService;
use App\Services\PayService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function test(
        RechargeService $rechargeService,
    WithdrawalService $withdrawalService,
    Request $request
    ){
//       $order_no = '202012021633299414236929';
//        $order_no = '202012021721455575793327';
//        $res =  $rechargeService->orderQuery($order_no);
//        return $res;
//
//       $order_no = '202012021647087553622134';
//       $res =  $rechargeService->test2($order_no);
//        return $res;
        $money = -384384;
        dd(abs($money));

        $request->bank_id = 29;
        $request->money = 100;

        $res =   $withdrawalService->testTix($request);
        return $res;
    }
}
