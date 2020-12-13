<?php

namespace App\Http\Controllers;

use App\Services\Game\Ssc_TwoService;
use App\Services\Game\SscService;
use App\Services\Pay\Winpay;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function test2(Winpay $winpay)
    {
        echo 123;die;
        $pay_type = '222';
        $money = 500;
        return ($winpay->rechargeOrder($pay_type, $money));
    }

    public function test(){
        $phone = request()->input('phone');
        $res = Redis::set("REGIST_CODE:" . $phone, 666666);
        dd($res);
    }

    public function openGame(SscService $sscService, Ssc_TwoService $ssc_TwoService){
        $sscService->ssc_ki(1);
    }
}
