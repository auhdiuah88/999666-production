<?php


namespace App\Services\Api;


use App\Common\Common;
use App\Repositories\Api\UserRepository;
use App\Services\Library\Auth;
use App\Services\Library\Netease\IM;
use App\Services\Library\Netease\SMS;
use App\Services\Library\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Ramsey\Uuid\Uuid;

class UserService
{
    protected $UserRepository;

    // 注册redis键
    const REDIS_REGIST_CODE = "REGIST_CODE:";    // redis短信验证码key
    // 找回密码redis键
    const REDIS_RETRIEVE_PWD_CODE = "RETRIEVE_PWD_CODE:";
    // redis短信验证码过期时间
    const REDIS_CODE_TTL = 120;

    // 验证码type 0注册短信 1重置密码短信
    const MESSAGE_REGISTER = 0;
    const MESSAGE_REPASS = 1;

    // 登录
    const  LOGIN_FORBIDDEN_ERR_FREQUENCY = 5;  // 登录禁用错误次数
    const  LOGIN_FORBIDDEN_DURATION = 600;     // 登录禁用时长（10分钟）


    public $error = '';
    public $error_code = 200;
    public $data = [];

    public $uploadService;
    private $im;
    private $Auth;
    private $sms;

    public function __construct(UserRepository $userRepository)
    {
        $this->UserRepository = $userRepository;

    }

    /**
     * 登录接口
     * @param $request
     * @return false|string
     */
    public function Login($params)
    {
        $userObj = $this->UserRepository->getUser($params['phone']);
        if (empty($userObj)) {
            $this->error_code = 402;
            $this->error = '用户不存在，请先注册用户';
            return false;
        }

        if ($userObj->is_login == 0) {
            $this->error_code = 403;
            $this->error = '帐户已被封禁，不允许登录';
            return false;
        }

        if (Crypt::decrypt($userObj->password) != $params['password']) {

            $this->error_code = 402;
            $this->error = '密码错误，清重试';
            return false;
        }
        $userModifyData = [
            'token' => Crypt::encrypt($userObj->id . "+" . time()),
            'last_time' => time()
        ];
        $userObj = $this->UserRepository->updateUser($userObj->id, $userModifyData);
        $this->data = $userObj;
        return true;
    }


    /**
     * 退出接口
     * @param $id
     */
    public function Out()
    {
        $objUser = $this->Auth->getUser();
        $this->UserRepository->setToken($objUser->id, '');
        $this->UserRepository->updateStatus($objUser->id, 0);
    }

    /**
     * 注册：验证短信验证码
     * @param $code 短信验证码
     * @return bool
     */
    public function ValidatorSms($phone, $messageCode)
    {
        $code = Redis::get(self::REDIS_REGIST_CODE . $phone);
        if (!$code) {
            $this->error_code = 414;
            $this->error = '短信验证码不存在或已过期';
            return false;
        }

        if ($code !== $messageCode) {
            $this->error_code = 402;
            $this->error = '短信验证码错误';
            return false;
        }
        return true;
    }

    /*
     * 重置密码
     */
    public function Retrieve_Pwd($data)
    {
        $code = Redis::get(self::REDIS_RETRIEVE_PWD_CODE . $data['phone']);
        if ($code) {
            if ($code !== $data['code']) {
                return $data = array("code" => 402,
                    "msg" => "短信验证码错误",
                    "data" => null);
            } else {
                $pasword = Crypt::encrypt($data['password']);
                $return = $this->UserRepository->UpPwd($data['phone'], $pasword);
                return $return;
            }
        } else {
            return $data = array(
                "code" => 414,
                "msg" => "短信验证码已过期",
                "data" => null);
        }
    }

    /**
     * 注册到我们的数据库中
     * @param $data
     * @return bool
     */
    public function Register($data)
    {
        $Count = $this->UserRepository->Count($data['phone']);
        if ($Count > 0) {
            $this->error_code = 401;
            $this->error = '此帐号已存在';
            return false;
        }
        if ($data["sms_code"] != Redis::get(self::REDIS_REGIST_CODE . $data["phone"])) {
            $this->error_code = 401;
            $this->error = "手机验证码错误";
            return false;
        }
        unset($data["sms_code"]);
        if(isset($data["code"])){
            if($data["code"]=="" || empty($data["code"])){
                unset($data["code"]);
            }
        }

        // 判断是否有代理
        if (array_key_exists("code", $data)) {
            $list = $this->UserRepository->findAgentByCode($data["code"]);
            unset($data["code"]);
            if ($list["user"]->is_customer_service == 1) {
                $data["customer_service_id"] = $list["user"]->id;
            } else {
                $agent = $list["agent"];
                if (isset($agent["one_id"])) {

                    $data["one_recommend_id"] = $agent["two_id"]; // null
                    $data["two_recommend_id"] = $agent["one_id"];

                    $one = $this->UserRepository->findByIdUser($agent["one_id"]);
                    $data["two_recommend_phone"] = $one->phone;

                    if (isset($agent["two_id"])) {
                        $two = $this->UserRepository->findByIdUser($agent["two_id"]);
                        $data["one_recommend_phone"] = $two->phone;
                    }

                }

            }
        }
        $data["password"] = Crypt::encrypt($data["password"]);
        $data["nickname"] = "用户" . md5($data['phone']);
        $data["reg_time"] = time();
        $data["reg_source_id"] = 0;
        $data["is_login"] = 1;
        $data["is_transaction"] = 1;
        $data["is_recharge"] = 1;
        $data["is_withdrawal"] = 1;
        $data["is_withdrawal"] = 1;
        $data["code"] = $this->UserRepository->getcode();


        $objUser = $this->UserRepository->createUser($data);

        return true;
    }

    /**
     * 短信验证码
     * @param $type 0:注册 1:找回密码
     * @return bool
     */
    public function sendMessage($phone, $type)
    {
        if ($type == self::MESSAGE_REGISTER) {
            if ($this->UserRepository->getUser($phone)) {
                $this->error_code = 414;
                $this->error = '该用户已存在';
                return false;
            }
            $key = self::REDIS_REGIST_CODE;
        } else if ($type == self::MESSAGE_REPASS) {
            $key = self::REDIS_RETRIEVE_PWD_CODE;
        }

        if (Redis::exists($key . $phone)) {
            $this->error_code = 414;
            $this->error = '请稍后再试';
            return false;
        }
        $result = $this->sendcode($phone);
        if ($result['code'] <> 200) {
            $this->error_code = 414;
            $this->error = 'Failed to send SMS verification code';
            return false;
        }
        Redis::set($key . $phone, $result["obj"]);
        Redis::expire($key . $phone, self::REDIS_CODE_TTL);

//        Log::channel('mytest')->info('发送短信验证码', $result);

        $this->error = '发送成功，' . (self::REDIS_CODE_TTL / 60) . '分钟内有效';
        $this->data = $result;
        return true;
    }

    public function sendCode($phone)
    {
        $url = "http://sms.skylinelabs.cc:20003/sendsmsV2";
        $phone = "91" . $phone;
        $account = "cs_aheln9";
        $sign = md5($account . "u2AGYncI" . date("YmdHis"));
        $code = mt_rand(100000, 999999);
        $context = urlencode("【sky-shop】您的验证码是" . $code);
        $params = [
            "account" => $account,
            "sign" => $sign,
            "numbers" => $phone,
            "content" => $context,
            "datetime" => date("YmdHis")
        ];
        $result = Http::post($url, $params)->json();

        if ($result["status"] == 0) {
            return ["code" => 200, "obj" => $code];
        }

        return ["code" => 402];
    }

    /**
     * 更新个人资料
     * @param $data
     * @return mixed
     */
    public function update(Request $request)
    {
        if (empty($request->all())) {
            $this->error = '请选择一个需要修改的';
            return false;
        }

        $objUser = $this->Auth->getUser();
        $params = $request->all();

        if ($request->hasFile('head_image') && $request->file('head_image')->isValid()) {
            Common::deleteImage($objUser->head_image);
            $params['head_image'] = $this->uploadService->uploadFile($request->file('head_image'), 'head_image');
        }

        //开启事务
        DB::beginTransaction();
        try {
            // 更新数据库以及缓存用户信息
            $objUser = $this->UserRepository->updateUser($objUser->id, $params);
            // 更新IM用户名片
            $this->UserRepository->updateImUser($objUser);

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();

            $this->error = '更新失败';
            return false;
        }

        return $objUser;
    }


    /**
     * 通过用户ID查找用户
     * @param $id
     * @return mixed
     */
    public function FindById($id)
    {
        $data = $this->UserRepository->cacheUser($id);
        unset($data->password);
        return $data;
    }

    public function Relieve($id)
    {
        return $this->UserRepository->updateStatus($id, 0);
    }

    public function Recommend($data)
    {
        return $this->UserRepository->addRecommend($data);
    }

    //用户提现
    public function Withdrawal($data)
    {
        //检查用户余额是否足够
        $user = $this->Auth->getUser();
        if ($user->balance < $data['money'] || $data['money'] < 100) {
            return false;
        }
        //检查用户是否拥有该张银行卡
        if ($this->UserRepository->isBank($user->id, $data['bank_id']) < 1) {
            return false;
        }
        //冻结用户金额并添加提现记录
        return $this->UserRepository->Withdrawal($user->id, $data['bank_id'], $data['money'], $user);


    }

    //获取用户提现历史
    public function Withdrawal_List($limit, $page)
    {
        $user = $this->Auth->getUser();
        return $this->UserRepository->Withdrawal_List($limit, ($page - 1) * $limit, $user->id);
    }


}
