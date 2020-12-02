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

//       $res =  $rechargeService->test($request->ip());
       $res =  $rechargeService->test2($request->ip());
//        dd($res);
        return $res;
    }
}
