<?php

namespace App\Http\Controllers;

use App\Repositories\Api\UserRepository;
use App\Services\Api\RechargeService;
use App\Services\Api\WithdrawalService;
use App\Services\Pay\PayStrategy;
use App\Services\PayService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function test2(
        RechargeService $rechargeService,
    WithdrawalService $withdrawalService,
    Request $request
    ){
        dump(env('APP_URL'));
        dump(asset('APP_URL'));
        dump(config('app.url'));
    }
}
