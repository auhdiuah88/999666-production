<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function AppReturn($code, $msg, $data = [])
    {
        return response()->json(
            [
                "code" => $code,
                "msg" => $msg,
                "data" => is_array($data)? $data : ($data?: new \StdClass())
            ]
        )->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    public function logError($channel, \Exception $e){
        $data = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'msg' => $e->getMessage()
        ];
        Log::channel($channel)->error(request()->method(), $data);
    }
}
