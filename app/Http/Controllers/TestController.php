<?php

namespace App\Http\Controllers;

use App\Models\Cx_User;
use App\Services\Pay\Leap;
use App\Services\Pay\PayStrategy;
use App\Services\Pay\Winpay;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function test2( ) {
//        $pay_type = '';
//        $money = 500;
//        $winpay->rechargeOrder($pay_type, $money);
       return Winpay::$snek;
        return Winpay::$secretkey;
    }
}