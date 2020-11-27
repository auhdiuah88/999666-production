<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function AppReturn($code, $msg, $data = [])
    {
        return response()->json(
            [
                "code" => $code,
                "msg" => $msg,
                "data" => $data ?: new \StdClass()
            ]
        )->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
}
