<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\PeriodService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PlanTaskExport;

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

    public function planTaskList()
    {
        try{
            $validator = Validator::make(request()->input(),
                [
                    'game_id' => ['required', 'integer', Rule::in(0,1,2,3,4)],
                    'page' => ['required', 'integer', 'gte:1'],
                    'size' => ['required', 'integer', Rule::in(1,50,100,200,400)]
                ]
            );
            if($validator->fails())
                return $this->AppReturn(402,$validator->errors()->first());
            $this->PeriodService->planTaskList();
            return $this->AppReturn(
                $this->PeriodService->_code,
                $this->PeriodService->_msg,
                $this->PeriodService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr',$e);
            return $this->AppReturn(402,$e->getMessage());
        }
    }

    public function exportTask()
    {
        try{
            $validator = Validator::make(request()->input(),
                [
                    'game_id' => ['required', 'integer', Rule::in(0,1,2,3,4)],
                    'page' => ['required', 'integer', 'gte:1'],
                    'size' => ['required', 'integer', Rule::in(1,50,100,200,400)]
                ]
            );
            if($validator->fails())
                return $this->AppReturn(402,$validator->errors()->first());
            $this->PeriodService->exportTask();
            return Excel::download(new PlanTaskExport($this->PeriodService->_data), '计划任务数据-'. date('YmdHis') .'.xlsx');
//            return response()->download($this->PeriodService->_data);
        }catch(\Exception $e){
            $this->logError('adminerr',$e);
            return $this->AppReturn(402,$e->getMessage());
        }
    }
}
