<?php

namespace App\Http\Controllers;

use App\Services\Api\RechargeService;
use App\Services\PayService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

class TestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function test(RechargeService $rechargeService
    ){
//        return $rechargeService->orderQuery('202011241459256363921725','696202011241459266672');
//        "sign":"2463f17f8400c0416d0dd86c28208508"
        $json = '{"money":"54.36","out_trade_no":"202011281743443450333436","pltf_order_id":"2559202011281743444014","rtn_code":"success"}';
        $params = json_decode($json, true);
        $res = PayService::generateSign($params);
        dd($res);
    }
}
