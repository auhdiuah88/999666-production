<?php


namespace App\Services\Api;


use App\Common\Common;
use App\Dictionary\SettingDic;
use App\Repositories\Api\SettingRepository;
use App\Repositories\Api\UserRepository;
use App\Services\Library\Auth;
use App\Services\Library\Netease\IM;
use App\Services\Library\Netease\SMS;
use App\Services\Library\Upload;
use App\Services\Message\MessageContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Ramsey\Uuid\Uuid;

class UserService
{
    protected $UserRepository, $SettingRepository;

    protected $messageContext;

    // 注册redis键
    const REDIS_REGIST_CODE = "REGIST_CODE:";    // redis短信验证码key
    // 找回密码redis键
    const REDIS_RETRIEVE_PWD_CODE = "RETRIEVE_PWD_CODE:";
    // 忘记密码redis键
    const REDIS_FORGET_PWD_CODE = "FORGET_PWD_CODE:";
    // redis短信验证码过期时间
    const REDIS_CODE_TTL = 1800;

    // 验证码type 0注册短信 1重置密码短信
    const MESSAGE_REGISTER = 0;
    const MESSAGE_REPASS = 1;
    const FORGET_PASS = 2;

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

    public function __construct
    (
        UserRepository $userRepository,
        SettingRepository $settingRepository,
        MessageContext $messageContext
    )
    {
        $this->UserRepository = $userRepository;
        $this->SettingRepository = $settingRepository;
        $this->messageContext = $messageContext;
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
            $this->error = 'User does not exist, please register user first';
            return false;
        }

        if ($userObj->is_login == 0) {
            $this->error_code = 403;
            $this->error = 'Account has been banned, login is not allowed';
            return false;
        }

        if (Crypt::decrypt($userObj->password) != $params['password']) {

            $this->error_code = 402;
            $this->error = 'Incorrect password, please try again';
            return false;
        }
        $token = Crypt::encrypt($userObj->id . "+" . time());
        $userModifyData = [
            'token' => $token,
            'last_time' => time()
        ];
        cache()->set(md5('usertoken' . $userObj->id), $token, 7 * 24 * 60 * 60);
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
            $this->error = 'The SMS verification code does not exist or has expired';
            return false;
        }

        if ($code !== $messageCode) {
            $this->error_code = 402;
            $this->error = 'The SMS verification code is incorrect';
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
                    "msg" => "The SMS verification code is incorrect",
                    "data" => null);
            } else {
                $pasword = Crypt::encrypt($data['password']);
                $return = $this->UserRepository->UpPwd($data['phone'], $pasword);
                return $return;
            }
        } else {
            return $data = array(
                "code" => 414,
                "msg" => "The SMS verification code has expired",
                "data" => null);
        }
    }

    /**
     * 忘记密码修改
     * @param $data
     * @return array
     */
    public function forgetPass($data)
    {
        $code = Redis::get(self::REDIS_FORGET_PWD_CODE . $data['phone']);
        if ($code) {
            if ($code !== $data['code']) {
                return array("code" => 402,
                    "msg" => "The SMS verification code is incorrect",
                    "data" => null);
            } else {
                $pasword = Crypt::encrypt($data['password']);
                $return = $this->UserRepository->UpPwd($data['phone'], $pasword);
                return $return;
            }
        } else {
            return array(
                "code" => 414,
                "msg" => "The SMS verification code has expired",
                "data" => null);
        }
    }

    /**
     * 注册到我们的数据库中
     * @param $data
     * @return bool
     */
    public function Register($data, $ip)
    {
        ##检查ip区域
        if(env('IS_LIMIT_IP',false) && !ipCheck($ip))
        {
            $this->error_code = 402;
            $this->error = "IP[{$ip}] is not in the valid area";
            return false;
        }

        if($this->SettingRepository->getIpSwitch() && $this->UserRepository->ipExist($ip)){
            $this->error_code = 402;
            $this->error = 'IP already exists [' . $ip . ']';
            return false;
        }
        $Count = $this->UserRepository->Count($data['phone']);
        if ($Count > 0) {
            $this->error_code = 402;
            $this->error = 'This account already exists';
            return false;
        }
        $is_check_sms_code = env('IS_CHECK_SMS_CODE',true);
        if ($is_check_sms_code && $data["sms_code"] != Redis::get(self::REDIS_REGIST_CODE . $data["phone"])) {
            $this->error_code = 402;
            $this->error = "The phone verification code is incorrect";
            return false;
        }
        unset($data["sms_code"]);
        if (isset($data["code"])) {
            if ($data["code"] == "" || empty($data["code"])) {
                unset($data["code"]);
            }
        }

        // 判断是否有代理
        if (array_key_exists("code", $data)) {
            $list = $this->UserRepository->findAgentByCode($data["code"]);
            if (empty($list)) {
                $this->error_code = 414;
                $this->error = 'Invitation user does not exist';
                return false;
            }
            unset($data["code"]);

            ##增加邀请关系
            if(!$list["user"]){
                $this->error_code = 414;
                $this->error = 'Invitation user does not exist.';
                return false;
            }
            $data["invite_relation"] = makeInviteRelation($list["user"]->invite_relation, $list["user"]->id);

            if ($list["user"]->is_customer_service == 1) {
                $data["customer_service_id"] = $list["user"]->id;
            } else {
                $agent = $list["agent"];
                if (isset($agent["one_id"])) {
                    $data["one_recommend_id"] = $agent["two_id"]; // null
                    $data["two_recommend_id"] = $agent["one_id"];
                    DB::beginTransaction();
                    try {
                        $one = $this->UserRepository->findByIdUser($agent["one_id"]);
                        $data["two_recommend_phone"] = $one->phone;
                        $one->one_number = $one->one_number + 1;
                        $one = $one->toArray();
                        $this->UserRepository->updateAgentMoneyRegister($one);

                        if (isset($agent["two_id"])) {
                            $two = $this->UserRepository->findByIdUser($agent["two_id"]);
                            $data["one_recommend_phone"] = $two->phone;
                            $two->two_number = $two->two_number + 1;
                            $two = $two->toArray();
                            $this->UserRepository->updateAgentMoneyRegister($two);
                        }
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error_code = $e->getCode();
                        $this->error = $e->getMessage();
                        return false;
                    }
                }

            }
        }
        $data["password"] = Crypt::encrypt($data["password"]);
        $data["nickname"] = "account" . md5($data['phone']);
        $data["reg_time"] = time();
        $data["reg_source_id"] = 0;
        $data["is_login"] = 1;
        $data["is_transaction"] = 1;
        $data["is_recharge"] = 1;
        $data["is_withdrawal"] = 1;
        $data["is_withdrawal"] = 1;
        $data["ip"] = $ip;
        $data["code"] = $this->UserRepository->getcode();

        ##判断用户是否是新代理模式邀请的用户
        if(isset($data['en']))
        {
            $en = $data['en'];
            unset($data['en']);
            if($en)
            {
                $en = Crypt::decryptString($en);
                if($en)
                {
                    $en = trim($en,'&');
                    $enArr = explode('&',$en);
                    $rateArr = explode('=', $enArr[0]);
                    $data['rebate_rate'] = $rateArr[1];
                    $userTypeArr = explode('=', $enArr[1]);
                    $data['user_type'] = $userTypeArr[1];
                }
            }
        }

        $user_id = $this->UserRepository->createUser($data);
        $userObj = $this->UserRepository->findByIdUser($user_id);

        ##注册返利
        $config = $this->SettingRepository->getSettingValueByKey(SettingDic::key('REGISTER'));
        $this->UserRepository->registerRebate($userObj, $config);

        $token = Crypt::encrypt($userObj->id . "+" . time());
        $userModifyData = [
            'token' => $token,
            'last_time' => time()
        ];
        cache()->set(md5('usertoken' . $userObj->id), $token, 7 * 24 * 60 * 60);
        $userObj = $this->UserRepository->updateUser($userObj->id, $userModifyData);
        $this->data = $userObj;
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
                $this->error = 'The user already exists';
                return false;
            }
            $key = self::REDIS_REGIST_CODE;
        } else if ($type == self::MESSAGE_REPASS) {
            $key = self::REDIS_RETRIEVE_PWD_CODE;
        } else if ($type == self::FORGET_PASS) {
            if (!$this->UserRepository->getUser($phone)) {
                $this->error_code = 414;
                $this->error = 'The user does not exists';
                return false;
            }
            $key = self::REDIS_FORGET_PWD_CODE;
        }

        if (Redis::exists($key . $phone)) {
            $this->error_code = 414;
            $this->error = 'Please try again later';
            return false;
        }

        $ip = \request()->ip();
        $ipKey = $key . str_replace('.','_', $ip);
//        if (Redis::exists($ipKey)) {
//            $this->error_code = 414;
//            $this->error = 'Invalid ip, Please try again later';
//            return false;
//        }

        $result = $this->sendcode($phone);
        if ($result['code'] <> 200) {
            $this->error_code = 414;
            $this->error = 'Failed to send SMS verification code';
            return false;
        }
        Redis::set($key . $phone, $result["obj"]);
        Redis::expire($key . $phone, self::REDIS_CODE_TTL);

        ##存入IP请求

        Redis::set($ipKey, date('Y-m-d H:i:s'));
        Redis::expire($ipKey, self::REDIS_CODE_TTL);

//        Log::channel('mytest')->info('发送短信验证码', $result);

        $m = self::REDIS_CODE_TTL / 60;
        $this->error = 'The send was successful，' . "Effective within {$m} minutes";
        $this->data = $result;
        return true;
    }

    public function sendCodeOld($phone)
    {
        $url = "http://sms.skylinelabs.cc:20003/sendsmsV2";
        $phone = "91" . $phone;
        $account = "cs_aheln9";
        $sign = md5($account . "u2AGYncI" . date("YmdHis"));
        $code = mt_rand(100000, 999999);
        $context = urlencode("【sky-shop】Your verification code is " . $code);
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

    public function sendCode($phone): array
    {
        $country = env('COUNTRY','india');
        $messageObj = $this->messageContext->getStrategy($country);
        if(!$messageObj){
            return ['code' => 414];
        }
        return $messageObj->sendRegisterCode($phone);
    }

    /**
     * 更新个人资料
     * @param $data
     * @return mixed
     */
    public function update(Request $request)
    {
        if (empty($request->all())) {
            $this->error = 'Please select one that needs to be modified';
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

            $this->error = 'The update failed';
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

    //用户余额
    public function getBalance($id)
    {
        return (float)($this->UserRepository->balance($id));
    }

    public function bankList()
    {
        $county = env('COUNTRY','india');
        $where = [
            'status' => ['=', 1]
        ];
        $type = 0;
        switch($county){
            case 'india':
                $type = 1;
                break;
            case 'vn':
                $type = 2;
                break;
            case 'br':
                $type = 3;
                break;
        }
        $where['type'] = ['=', $type];
        return $this->UserRepository->bankList($where);
    }

    public function getPersonalService(): array
    {
        $data = [
            'whats_app_account' => '',
            'whats_app_link' => ''
        ];
        $user = request()->get('userInfo');
        if(!$user['invite_relation'])
        {
            return $data;
        }
        $relation = explode('-',trim($user['invite_relation'],'-'));
        $leader_id = (int)$relation[count($relation)-1];
        $leader = $this->UserRepository->getUserWhatsApp($leader_id);
        if(!$leader)
        {
            return $data;
        }
        return [
            'whats_app_account' => $leader->whats_app_account,
            'whats_app_link' => $leader->whats_app_link
        ];
    }

}
