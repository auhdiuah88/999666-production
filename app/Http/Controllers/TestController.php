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
use Illuminate\Support\Facades\Crypt;

class TestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function test(
        RechargeService $rechargeService,
        WithdrawalService $withdrawalService,
        Request $request
    )
    {
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

        $res = $withdrawalService->testTix($request);
        return $res;
    }

    public function test2()
    {
        dd(Crypt::decrypt("eyJpdiI6IlNjTW1yOWlZalRFQ2xQeGV0Zi9yMnc9PSIsInZhbHVlIjoiNU92a2FWS2JKR0dESDVDRDJJSk8zN05RMWlEN1YvVjRQNFNOa1k0VkpCaz0iLCJtYWMiOiI4M2ExZDEwMWU4M2Q5MzJlNmM4NWIzMzI3ODY1YzcyNWUwNmQyY2VjOTM1ZWJiNzJiMjRiNzg4ZGIzYmQ4ODQyIn0="));
    }
}
