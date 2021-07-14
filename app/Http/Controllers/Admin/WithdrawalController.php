<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\WithdrawalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WithdrawalController extends Controller
{
    private $WithdrawalService;

    public function __construct(WithdrawalService $withdrawalService)
    {
        $this->WithdrawalService = $withdrawalService;
    }

    public function findAll(Request $request)
    {
        $this->WithdrawalService->findAll($request->get("page"), $request->get("limit"), $request->get("status"));
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }

    /**
     * 实时审核列表
     */
    public function syncInRealtime()
    {
        $retry = 10000;
        $result = $this->WithdrawalService->getNewests();
        $response = new StreamedResponse(function () use ($result, $retry) {
            echo "retry: {$retry}" . PHP_EOL . 'data: ' . json_encode($result) . "\n\n";
        });
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cach-Control', 'no-cache');
        return $response;
    }

    public function auditRecord(Request $request)
    {
        $this->WithdrawalService->auditRecord($request);
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }

    public function retry()
    {
        $validator = Validator::make(request()->input(), [
            'id' => 'required|integer|gte:1'
        ]);
        if($validator->fails())
            return $this->AppReturn(
                402,
                $validator->errors()->first()
            );
        $this->WithdrawalService->retry();
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }

    public function batchPassRecord(Request $request)
    {
        $this->WithdrawalService->batchPassRecord($request->post());
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }

    public function batchFailureRecord(Request $request)
    {
        $this->WithdrawalService->batchFailureRecord($request->post());
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }

    public function searchRecord(Request $request)
    {
        $this->WithdrawalService->searchRecord($request->post());
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }

    /**
     * 实时审核通知
     */
    public function syncInRealtimeNotice()
    {
        $retry = 10000;
        $result = $this->WithdrawalService->getNewest();
        $response = new StreamedResponse(function () use ($result, $retry) {
            echo "retry: {$retry}" . PHP_EOL . 'data: ' . json_encode($result) . "\n\n";
        });
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cach-Control', 'no-cache');
        return $response;
    }

    /**
     * 取消代付失败订单
     */
    public function cancellationRefund(Request $request)
    {
        $this->WithdrawalService->cancellationRefund($request->post("id"));
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }

    /**
     * 切换代付通道
     */
    public function exchangeChannel()
    {
        $validator = Validator::make(request()->input(), [
            'id' => 'required|integer|gte:1',
            'with_type' => 'required'
        ]);
        if($validator->fails())
            return $this->AppReturn(
                402,
                $validator->errors()->first()
            );
        $this->WithdrawalService->exchangeChannel();
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }

    /**
     * 代付通道
     */
    public function channels()
    {
        $this->WithdrawalService->channels();
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }
}
