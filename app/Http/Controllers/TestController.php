<?php

namespace App\Http\Controllers;

use App\Services\Api\RechargeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

class TestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function test(RechargeService $rechargeService){
        return $rechargeService->orderQuery('202011241459256363921725','696202011241459266672');
    }
}
