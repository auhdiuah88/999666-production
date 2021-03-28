<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\Aes;
use App\Services\Api\RechargeService;
use App\Services\Api\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class RechargeController extends Controller
{
    protected $UserService, $rechargeService;


    public function __construct(UserService $userService,
                                RechargeService $rechargeService
    )
    {
        $this->UserService = $userService;
        $this->rechargeService = $rechargeService;
    }

    /**
     * 用户充值方式列表
     */
    public function rechargeMethods(Request $request) {
//        $rules = [ '' ];
//        $validator = Validator::make($request->post(), $rules);
//        if ($validator->fails()) {
//            return $this->AppReturn(414, $validator->errors()->first());
//        }
//        $host = $request->getHost();
//        $provider = explode('.', $host);
//        $result = config('pay.pay_provider.'.$provider[1]);
//        if (empty($result)){
//            $this->AppReturn(400, 'please set recharge method',$result);
//        }
        $this->rechargeService->getConfig();
        $result = $this->rechargeService->_data;
        ##获取用户余额
        $balance = $this->UserService->getBalance($request->get('userInfo')['id']);
//        $balance = $request->get('userInfo')['balance'];

        $res = compact('balance');
        $res['recharge_method'] = $result;

        return $this->AppReturn(200, 'recharge method', $res);
    }

  /**
     * 用户充值-请求充值订单-二维码 （充值界面提交）
     */
    public function recharge(Request $request)
    {
        $rules = [
            "money" => "required|integer",
            "pay_type" => "required",    // 充值方式 如 bank,upi
        ];
        $validator = Validator::make($request->post(), $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        if (!$result = $this->rechargeService->rechargeOrder($request)) {
            return $this->AppReturn(400, $this->rechargeService->_msg, new \StdClass());
        }
        return $this->AppReturn(200, '用户充值-充值订单二维码', $result);
    }

    /**
     * 充值记录
     */
    public function rechargeLog(Request $request)
    {
        $rules = [
            "status" => "required|integer|in:1,2,3",
            "page" => "required|integer|min:1",
            "limit" => "required|integer",
        ];
        $validator = Validator::make($request->post(), $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        return $this->AppReturn(200, '充值记录:', $this->rechargeService->rechargeLog($request));
    }

    /**
     * 充值回调接口
     */
    public function rechargeCallback(Request $request)
    {
        try{
            if ($this->rechargeService->rechargeCallback($request)) {
                if($this->rechargeService->rtn){
                    return $this->rechargeService->rtn;
                }
                $payProvide = $request->input('type', '');
                if($payProvide == 'rspay')
                    return 'OK';
                elseif ($payProvide == 'inpays')
                    return 'ok';
                return 'success';
            }
            Log::channel('kidebug')->error('recharge_callback', ['message'=>$this->rechargeService->_msg]);
            return $this->rechargeService->_msg;
        }catch(\Exception $e){
            Log::channel('kidebug')->error('recharge_callback', ['file'=>$e->getFile(),'line'=>$e->getLine(), 'message'=>$e->getMessage(), 'data'=>$request->all()]);
            return false;
        }

    }

    public function rechargeConfirm(Request $request)
    {
        $confirm_recharge_log = env('CONFIRM_RECHARGE_LOG', false);
        if (!$confirm_recharge_log) {
            return $this->AppReturn(403, 'forbidden');
        }

        $rules = [
            "order_no" => [
                "required",
                Rule::exists('user_recharge_logs')->where(function ($query) use ($request) {
                    $query->where([
                        ['order_no', '=', $request->input('order_no')],
                        ['status', '=', 1]
                    ]);
                }),
            ],
        ];
        $validator = Validator::make($request->post(), $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        return $this->AppReturn(200, 'success', $this->rechargeService->rechargeConfirm($request));
    }

    public function matthewCallback()
    {
        $validator = Validator::make(request()->input(), [
            'encryptedData' => ['required'],
            'type' => ['required']
        ]);
        if($validator->fails())
            return $validator->errors()->first();
        $type = request()->input('type');
        $rechargeConfig = DB::table('settings')->where('setting_key','recharge')->value('setting_value');
        $rechargeConfig && $rechargeConfig = json_decode($rechargeConfig,true);
        $rechargeMerchantID = isset($rechargeConfig[$type])?$rechargeConfig[$type]['merchant_id']:"";
        $rechargeSecretKey = isset($rechargeConfig[$type])?$rechargeConfig[$type]['secret_key']:"";
        $Aes = new Aes();
        $key = substr($rechargeSecretKey, 0,16);
        $encryptedData = request()->input('encryptedData');
        $data = $Aes->decryptWithOpenssl($key, $encryptedData, '!WFNZFU_{H%M(S|a');   //提现数据加密
        if(!$data){
            return '解密失败';
        }
    }

    public function platBankCards()
    {
        $this->rechargeService->platBankCards();
        return $this->AppReturn($this->rechargeService->_code, $this->rechargeService->_msg, $this->rechargeService->_data);
    }

    public function requestDirectRecharge()
    {
        $validator = Validator::make(request()->post(),
            [
                'bank_card_id' => ['required', 'gte:1', 'integer'],
                'remark' => ['max:200'],
                'money' => ['required', 'gte:1']
            ]
        );
        if($validator->fails())
            return $this->AppReturn(402,$validator->errors()->first());
        $this->rechargeService->requestDirectRecharge();
        return $this->AppReturn($this->rechargeService->_code, $this->rechargeService->_msg, $this->rechargeService->_data);
    }

    public function requestDirectRechargeLogs()
    {
        $validator = Validator::make(request()->input(),
            [
                'page' => ['required', 'gte:1', 'integer'],
                'size' => ['required', 'gte:1', 'lte:10'],
                'status' => ['required', Rule::in(0,1,2)]
            ]
        );
        if($validator->fails())
            return $this->AppReturn(402,$validator->errors()->first());
        $this->rechargeService->requestDirectRechargeLogs();
        return $this->AppReturn(
            $this->rechargeService->_code,
            $this->rechargeService->_msg,
            $this->rechargeService->_data
        );
    }

}
