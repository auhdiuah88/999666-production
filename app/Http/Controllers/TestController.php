<?php

namespace App\Http\Controllers;

use App\Models\Cx_User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function test2(Request $request ) {
        $response = new StreamedResponse(function() use ($request) {
//            while(true) {
//                echo 'data: ' . json_encode(Cx_User::first()) . "\n\n";
                echo 'data: ' . 'kkkk'. "\n\n";
//                ob_flush();
//                flush();
//                usleep(200000);
//            }
        });
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cach-Control', 'no-cache');
        return $response;
    }

    public function test21()
    {
        $retry = 3000;
        $header = array(
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache'
        );
        return response("retry: {$retry}" . PHP_EOL."data: Time".date('H:i:s').PHP_EOL.PHP_EOL, 200, $header);
        return response("data: Time".date('H:i:s').PHP_EOL.PHP_EOL, 200, $header);
    }
}
