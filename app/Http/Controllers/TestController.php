<?php

namespace App\Http\Controllers;

use App\Repositories\Api\UserRepository;
use App\Services\Api\RechargeService;
use App\Services\PayService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function test(RechargeService $rechargeService,
    Request $request
    ){
//       $order_no = '202012021633299414236929';
//        $order_no = '202012021633299414236929';
//        $res =  $rechargeService->orderQuery($order_no);
//        return $res;

       $order_no = '202012021647087553622134';
       $res =  $rechargeService->test2($order_no);

        return $res;
    }
}
