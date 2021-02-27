<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\BettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BettingController extends Controller
{
    private $BettingService;

    public function __construct(BettingService $bettingService)
    {
        $this->BettingService = $bettingService;
    }

    public function findAll(Request $request)
    {
        $this->BettingService->findAll($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->BettingService->_code,
            $this->BettingService->_msg,
            $this->BettingService->_data
        );
    }

    /**
     *  订单信息实时推送  (使用 EventSource)
     */
    public function syncInRealtime(Request $request)
    {
        $retry = 10000;
        $result = $this->BettingService->getNewest();
        $response = new StreamedResponse(function() use ($result,$retry) {
            echo "retry: {$retry}" . PHP_EOL.'data: ' . json_encode($result) . "\n\n";
        });
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cach-Control', 'no-cache');
        return $response;
    }

    public function searchBettingLogs(Request $request)
    {
        $this->BettingService->searchBettingLogs($request->post());
        return $this->AppReturn(
            $this->BettingService->_code,
            $this->BettingService->_msg,
            $this->BettingService->_data
        );
    }

    public function statisticsBettingLogs()
    {
        $this->BettingService->statisticsBettingLogs();
        return $this->AppReturn(
            $this->BettingService->_code,
            $this->BettingService->_msg,
            $this->BettingService->_data
        );
    }

    /**
     * 投注提醒用户列表
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function noticeList()
    {
        $validator = Validator::make(request()->input(),
            [
                'page' => ['required', 'integer', 'gte:1'],
                'size' => ['required', 'integer', 'gte:1', 'lte:30'],
                'user_id' => ['integer', 'gte:0']
            ]
        );
        if($validator->fails())
            return $this->AppReturn(
                403,
                $validator->errors()->first()
            );
        $this->BettingService->noticeList();
        return $this->AppReturn(
            $this->BettingService->_code,
            $this->BettingService->_msg,
            $this->BettingService->_data
        );
    }

    public function noticeBettingList()
    {
        $validator = Validator::make(request()->input(),
            [
                'page' => ['required', 'integer', 'gte:1'],
                'size' => ['required', 'integer', 'gte:1', 'lte:30'],
                'user_id' => ['required', 'integer', 'gte:0'],
                'sort' => ['required', Rule::in(1,2)],
            ]
        );
        if($validator->fails())
            return $this->AppReturn(
                403,
                $validator->errors()->first()
            );
        $this->BettingService->noticeBettingList();
        return $this->AppReturn(
            $this->BettingService->_code,
            $this->BettingService->_msg,
            $this->BettingService->_data
        );
    }
}
