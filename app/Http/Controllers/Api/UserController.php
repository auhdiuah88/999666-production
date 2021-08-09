<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\UserBalanceService;
use App\Services\Api\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Libs\Games\GameContext;


class UserController extends Controller
{
    protected $UserService, $UserBalanceService,$GameContext;


    public function __construct
    (
        UserService $userService,
        UserBalanceService $userBalanceService,
        GameContext $GameContext
    )
    {
        $this->UserService = $userService;
        $this->UserBalanceService = $userBalanceService;
        $this->GameContext =$GameContext;
    }

    /**
     * 登录接口
     * @param Request $request
     * @return false|string
     */
    public function Login(Request $request)
    {
        try{
            $data = $request->post();
            $rules = [
                "phone" => "required",
                "password" => "required",
            ];
//        $massages = [
//            "phone.required" => "手机不能为空",
//            "password.required" => "密码不能为空",
//        ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->AppReturn(414, $validator->errors()->first());
            }

            if ($this->UserService->Login($data)) {
                return $this->AppReturn(200, 'login success', $this->UserService->data);
            }
            return $this->AppReturn($this->UserService->error_code, $this->UserService->error);
        }catch(\Exception $e){
            $this->logError('apidebug',$e);
            return $this->AppReturn(414, 'login fail');
        }
    }

    /**
     * 退出接口
     * @param Request $request
     * @return false|string
     */
    public function Out(Request $request)
    {
        $this->UserService->Out();
        return $this->AppReturn(200, 'logout success');
    }

    /**
     * 注册接口
     * @param Request $request
     * @return false|string
     */
    public function Register(Request $request)
    {
        try{
            // 参数验证
            $data = $request->post();
            $is_check_sms_code = env('IS_CHECK_SMS_CODE',true);
            $rules = [
                "phone" => "required|numeric",
                "password" => "required"
            ];
            if(!preg_match(getPhoneReg(),request()->post('phone')))
            {
                return $this->AppReturn(414, 'wrong phone number');
            }
            if($is_check_sms_code)
            {
                $rules['sms_code'] = "required";
            }
            $register_must_invite = config('site.register_must_invite',false);
            if($register_must_invite)
            {
                $rules['code'] = "required";
            }
//        $massages = [
//            "phone.required" => "用户名不能为空",
//            "password.required" => "密码不能为空",
//            "sms_code.required" => "手机验证码不能为空"
//        ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->AppReturn(414, $validator->errors()->first());
            }
            if (!$this->UserService->Register($data, $request->ip())) {
                return $this->AppReturn($this->UserService->error_code, $this->UserService->error);
            }
            return $this->AppReturn(200, 'success', $this->UserService->data);
        }catch(\Exception $e){
            Log::channel('kidebug')->debug('register_err',['error'=>$e->getMessage(), 'file'=>$e->getFile(), 'line'=>$e->getLine()]);
            return $this->AppReturn(414, "register failed");
        }
    }

    /**
     * 验证短信验证码
     * @param Request $request
     */
    public function ValidatorSms(Request $request)
    {
        $rules = [
            "phone" => "required",
            "code" => "required",
        ];
//        $massages = [
//            "phone.required" => "验证码不能为空",
//            "code.required" => "手机号码不能为空",
//        ];
        $validator = Validator::make($request->post(), $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        if ($this->UserService->ValidatorSms($request->post("phone"), $request->post("code"))) {
            return $this->AppReturn(200, 'Verified successfully');
        }
        return $this->AppReturn($this->UserService->error_code, $this->UserService->error);
    }

    /**
     * 短信验证码发送
     * @param Request $request
     */
    public function sendMessage(Request $request)
    {
        $rules = [
            "phone" => "required",
            "type" => "required",
        ];

        $validator = Validator::make($request->post(), $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        if ($this->UserService->sendMessage($request->post("phone"), $request->post("type"))) {
            return $this->AppReturn(200, $this->UserService->error);
        }
        return $this->AppReturn($this->UserService->error_code, $this->UserService->error);
    }

    //忘记密码重置
    public function resetPass(Request $request)
    {
        $data = $request->post();
        $rules = [
            "phone" => "required",
            "code" => "required",
            "password" => "required",
            "re_password" => "required|same:password",
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            $result = [
                "code" => 414,
                "massage" => $validator->errors()->all(),
                "data" => null
            ];
            return response()->json($result);     //显示所有错误组成的数组
        }
        $return = $this->UserService->forgetPass($data);
        return response()->json($return);
    }

    /**
     * 重置密码接口
     * @param Request $request
     */
    public function Retrieve_Pwd(Request $request)
    {
        $data = $request->post();
        $rules = [
            "phone" => "required",
            "code" => "required",
            "password" => "required",
        ];
//        $massages = [
//            "phone.required" => "手机号码不能为空",
//            "code.required" => "验证码不能为空",
//            "password.required" => "密码不能为空",
//        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            $result = [
                "code" => 414,
                "massage" => $validator->errors()->all(),
                "data" => null
            ];
            return json_encode($result, JSON_UNESCAPED_UNICODE);     //显示所有错误组成的数组
        }
        $return = $this->UserService->Retrieve_Pwd($data);
        return json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取个人资料
     * @param Request $request
     * @return false|string
     */
    public function profile(Request $request, Auth $auth)
    {
        $meObj = $auth::instance()->getUser();
        $meObj->IM_token = Redis::get("USER_IM_TOKEN:" . $meObj->id);
        return $this->AppReturn(200, 'My Profile', $meObj);
    }

    /**
     * 更新个人资料
     * @param Request $request
     * @return false|string
     */
    public function update(Request $request)
    {
        // 参数验证
        $rules = [
            "head_image" => "max:1024|image",
            'nickname' => 'required|between:2,10',
            'signature' => 'max:20',
            'job' => 'max:15',
            'area' => 'max:20',
            'sex' => 'in:1,2',
        ];
        $massages = [];
        $validator = Validator::make($request->all(), $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first(), new \StdClass());
        }

        if ($userInfo = $this->UserService->update($request)) {
            return $this->AppReturn(200, 'update success', $userInfo);
        } else {
            return $this->AppReturn(400, $this->UserService->error, new \StdClass());
        }
    }

    /**
     * 我的余额
     */
    public function balance()
    {
        $myObj = $this->Auth->getUser();
        $data = $this->KyqpService->Get_Money();
        $data1 = $this->LcylService->Get_Money();
        $data2 = $this->LyqpService->Get_Money();
        if ($data['s'] == 101 && $data['d']['code'] == 0) {
            $arr['ky_balance'] = $data['d']['money'];

        } else {
            $arr['ky_balance'] = 0;
        }
        if ($data1['s'] == 101 && $data1['d']['code'] == 0) {
            $arr['lc_balance'] = $data1['d']['money'];

        } else {
            $arr['lc_balance'] = 0;
        }
        if ($data2['s'] == 101 && $data2['d']['code'] == 0) {
            $arr['ly_balance'] = $data2['d']['money'];

        } else {
            $arr['ly_balance'] = 0;
        }
        $arr['balance'] = round($myObj->balance, 2);
        return $this->AppReturn(200, '成功', $arr);

    }

    /**
     * 我的关注
     */
    public function follows(Request $request)
    {
        // 参数验证
        $rules = [
            "page" => "required|integer|min:1",
            "limit" => "required|integer",
        ];
        $massages = [];
        $validator = Validator::make($request->all(), $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $data = $this->UserService->followUsers($request->get("limit"), $request->get("page"));
        return $this->AppReturn(200, '我的关注', $data);
    }

    /**
     * 我的粉丝
     * @param Request $request
     * @return UserController
     */
    public function fans(Request $request)
    {
        // 参数验证
        $rules = [
            "page" => "required|integer|min:1",
            "limit" => "required|integer",
        ];
        $massages = [];
        $validator = Validator::make($request->all(), $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $data = $this->UserService->fanUsers($request->get("limit"), $request->get("page"));
        return $this->AppReturn(200, '我的粉丝', $data);
    }

    /*
     * 用户/主播提现
     */
    public function Withdrawal(Request $request)
    {
        // 参数验证
        $rules = [
            "money" => "required|integer|min:100",
            "bank_id" => "required|integer",
        ];
//        $massages = [
//            "money.required" => "金额不能为空",
//            "money.integer" => "金额必须为整数",
//            "money.min" => "金额不得小于100",
//            "bank_id.required" => "银行卡不能为空",
//            "bank_id.integer" => "银行卡必须为整数",
//        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        if ($this->UserService->Withdrawal($request->all())) {
            return $this->AppReturn(200, 'Successful withdrawal application');
        } else {
            return $this->AppReturn(413, 'The withdrawal failed');
        }
    }

    public function Withdrawal_List(Request $request)
    {
        $rules = [
            "limit" => "required",
            "page" => "required",
        ];
//        $massages = [
//            "limit.required" => "条数不能为空",
//            "page.required" => "页数不能为空",
//        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $data = $this->UserService->Withdrawal_List($request->input("limit"), $request->input("page"));
        return $this->AppReturn(200, 'ok', $data);
    }

    public function bankList()
    {
        $data = $this->UserService->bankList();
        return $this->AppReturn(200, 'ok', $data);
    }

    public function personalService()
    {
        $data = $this->UserService->getPersonalService();
        return $this->AppReturn(200, 'ok', $data);
    }

    public function addBalanceLogList()
    {
        $this->UserBalanceService->getAddBalanceLogList();
        return $this->AppReturn
        (
            $this->UserBalanceService->_code,
            $this->UserBalanceService->_msg,
            $this->UserBalanceService->_data
        );
    }

    public function reduceBalanceLogList()
    {
        $this->UserBalanceService->getReduceBalanceLogList();
        return $this->AppReturn
        (
            $this->UserBalanceService->_code,
            $this->UserBalanceService->_msg,
            $this->UserBalanceService->_data
        );
    }

    //游戏平台列表接口
    public function PlatformList(Request $request){
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        $user_id = $data[0];
        $info = DB::table('users_wallet')->join("wallet_name","users_wallet.wallet_id","=","wallet_name.id")->where("user_id",$user_id)->get()->toArray();
        $info = json_decode(json_encode($info));
        if(!$info){
            return [
                "code" => 1,
                "msg" => "no wallet",
                "data" => ""
            ];
        }
        return [
            "code" => 200,
            "msg" => "success",
            "data" => $info
        ];
    }

    //上分
    public function TopScores(Request $request){
        $res = $request->input("p");
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        $user_id = $data[0];
        ##获取游戏信息
        $game = DB::table("game_list")->where("id",$res["game_id"])->select()->first();
        $link = $game->link;
        $Scores = $this->GameContext->getStrategy($link);
        if(!$Scores->TopScores($res["money"],$user_id))
        {
            $this->_msg = $Scores->_msg;
            $this->_code = 415;
            $this->_data = $Scores;
            return;
        }
        return $Scores->_data;
    }

    //下分
    public function LowerScores(Request $request){
        $res = $request->input("p");
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        $user_id = $data[0];
        ##获取游戏信息
        $game = DB::table("game_list")->where("id",$res["game_id"])->select()->first();
        $link = $game->link;
        $Scores = $this->GameContext->getStrategy($link);
        if(!$Scores->LowerScores($res["money"],$user_id))
        {
            $this->_msg = $Scores->_msg;
            $this->_code = 415;
            $this->_data = $Scores;
            return;
        }
        return $Scores->_data;
    }

    //查询余额
    public function QueryScore(Request $request){
        $res = $request->input("p");
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        $user_id = $data[0];
        ##获取游戏信息
        $wallet_name = DB::table("wallet_name")->where("id",$res["game_id"])->select()->first();
        $link = $wallet_name->wallet_name;
        $Scores = $this->GameContext->getStrategy($link);
        if(!$Scores->QueryScore($user_id))
        {
            $this->_msg = $Scores->_msg;
            $this->_code = 415;
            $this->_data = $Scores;
            return;
        }
        return $this->_data = $Scores;
    }
}
