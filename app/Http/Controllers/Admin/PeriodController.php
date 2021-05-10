<?php


namespace App\Http\Controllers\Admin;


use App\Exports\SDReader;
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
        $this->PeriodService->searchPeriod($request->input());
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

    public function getSDList()
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
            $this->PeriodService->getSDList();
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

    public function exportSD()
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
            $this->PeriodService->exportSD();
            return Excel::download(new PlanTaskExport($this->PeriodService->_data), 'SD-DEMO-'. date('YmdHis') .'.xlsx');
//            return response()->download($this->PeriodService->_data);
        }catch(\Exception $e){
            $this->logError('adminerr',$e);
            return $this->AppReturn(402,$e->getMessage());
        }
    }

    public function SDPrize()
    {
        if(!request()->hasFile('file')){
            return $this->AppReturn(402,'请上传批量手动开奖excel文件');
        }

        $excelUrl =request()->file("file")->store("/public/excel");
        $items = Excel::toArray(new SDReader(),$excelUrl,"","");
        if(empty($items) || !isset($items[0])){
            return $this->AppReturn(402,'非标准手动开奖excel文件');
        }
        print_r($items);die;
        foreach($items[0] as $key => $val){
            if($key == 0){
                ##检查
                if(count($val) != 6){
                    return $this->AppReturn(402,'非标准手动开奖excel文件');
                }
            }
            ##设置手动开奖结果
            $period_id = intval($val[0]);
            if($period_id <= 0)continue;
            $prize_number = $val[4];
            if($prize_number == "")continue;
            $prize_number = intval($prize_number);
            if(!in_array($prize_number,[0,1,2,3,4,5,6,7,8,9]))continue;
            $this->PeriodService->SDPrize($period_id, $prize_number);
        }
        return $this->AppReturn(200,'批量手动开奖设置成功');
    }

}
