<?php

namespace App\Http\Controllers;

use App\Models\Cx_User;
use App\Services\Pay\Leap;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected static $merchantID = '';     // 商户ID
    protected static $secretkey = '';      // 密钥

    public function test2(Request $request,Leap $leap ) {
        return $leap->testGetCallbackUrl();
    }
}
