<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\PeriodService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PeriodController extends Controller
{
    private $PeriodService;

    public function __construct(PeriodService $periodService)
    {
        $this->PeriodService = $periodService;
    }

    public function findAll(Request $request)
    {
        $this->PeriodService->findAll($request->get("page"), $request->get("limit"), $request->get("status"));
        return $this->AppReturn(
            $this->PeriodService->_code,
            $this->PeriodService->_msg,
            $this->PeriodService->_data
        );
    }

    /**
     *  活动列表  (使用 EventSource)
     */
    public function syncInRealtime(Request $request)
    {
        $retry = 10000;
        $result = $this->PeriodService->getNewest($request);
        $response = new StreamedResponse(function() use ($result,$retry) {
            echo "retry: {$retry}" . PHP_EOL.'data: ' . json_encode($result) . "\n\n";
        });
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cach-Control', 'no-cache');
        return $response;
    }

    public function searchPeriod(Request $request)
    {
        $this->PeriodService->searchPeriod($request->post());
        return $this->AppReturn(
            $this->PeriodService->_code,
            $this->PeriodService->_msg,
            $this->PeriodService->_data
        );
    }

    public function findById(Request $request)
    {
        $this->PeriodService->findById($request->get("id"));
        return $this->AppReturn(
            $this->PeriodService->_code,
            $this->PeriodService->_msg,
            $this->PeriodService->_data
        );
    }
}
