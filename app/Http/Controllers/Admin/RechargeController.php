<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\RechargeService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RechargeController extends Controller
{
    private $RechargeService;

    public function __construct(RechargeService $rechargeService)
    {
        $this->RechargeService = $rechargeService;
    }

    public function findAll(Request $request)
    {
        $this->RechargeService->findAll($request->get("page"), $request->get("limit"), $request->get("status"));
        return $this->AppReturn(
            $this->RechargeService->_code,
            $this->RechargeService->_msg,
            $this->RechargeService->_data
        );
    }

    public function searchRechargeLogs(Request $request)
    {
        $this->RechargeService->searchRechargeLogs($request->post());
        return $this->AppReturn(
            $this->RechargeService->_code,
            $this->RechargeService->_msg,
            $this->RechargeService->_data
        );
    }

    public function getUser(Request $request)
    {
        $this->RechargeService->getUser($request->post("id"));
        return $this->AppReturn(
            $this->RechargeService->_code,
            $this->RechargeService->_msg,
            $this->RechargeService->_data
        );
    }

    public function syncInRealtimeNotice()
    {
        $retry = 10000;
        $result = $this->RechargeService->getNewest();
        $response = new StreamedResponse(function () use ($result, $retry) {
            echo "retry: {$retry}" . PHP_EOL . 'data: ' . json_encode($result) . "\n\n";
        });
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cach-Control', 'no-cache');
        return $response;
    }
}
