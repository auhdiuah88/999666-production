<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\DirectRechargeService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RequestRechargeController extends Controller
{

    protected $DirectRechargeService;

    public function __construct
    (
        DirectRechargeService $directRechargeService
    )
    {
        $this->DirectRechargeService = $directRechargeService;
    }

    /**
     * 申请列表
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function lists()
    {
        $validator = Validator::make(request()->input(),
            [
                'page' => ['required', 'integer', 'gte:1'],
                'size' => ['required', 'integer', 'gte:1', 'lte:20'],
                'order_no' => ['between:10,20'],
                'phone' => ['between:8,12'],
                'user_id' => ['integer', 'gte:1'],
                'status' => ['required', Rule::in(-1,0,1,2)]
            ]
        );
        if($validator->fails())
            return $this->AppReturn(
                402,
                $validator->errors()->first()
            );
        $this->DirectRechargeService->lists();
        return $this->AppReturn(
          $this->DirectRechargeService->_code,
          $this->DirectRechargeService->_msg,
          $this->DirectRechargeService->_data
        );
    }

    public function exam()
    {
        try{
            $validator = Validator::make(request()->post(),
                [
                    'id' => ['required', 'integer', 'gte:1'],
                    'status' => ['required', 'integer', Rule::in(1,2)],
                    'message' => ['max:100'],
                    'real_money' => ['numeric', 'gte:0', 'lte:100000000']
                ]
            );
            if($validator->fails())
                return $this->AppReturn(
                    402,
                    $validator->errors()->first()
                );
            $this->DirectRechargeService->exam();
            return $this->AppReturn(
                $this->DirectRechargeService->_code,
                $this->DirectRechargeService->_msg,
                $this->DirectRechargeService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

}
