<?php


namespace App\Services;


use App\Services\Pay\PayStrategy;
use Illuminate\Support\Facades\Crypt;

/**
 *
 */
class PayService extends BaseService
{
//    protected static $url = 'http://ipay-in.yynn.me';
//    protected static $url = 'http://payqqqbank.payto89.com';  // 支付网关
//
//    protected static $url_cashout = 'http://tqqqbank.payto89.com:82'; // 提现网关
//
//    // 正式环境
//    protected static $merchantID = 262593573;
//    protected static $secretkey = '4e70f59ec59149a6b81d26aafed8f6fb';
//
//    /**
//     * 生成签名   sign = Md5(key1=vaIue1&key2=vaIue2&key=签名密钥);
//     */
//    public static function generateSign(array $params)
//    {
//        ksort($params);
//        $string = [];
//        foreach ($params as $key => $value) {
//            $string[] = $key . '=' . $value;
//        }
//        $sign = (implode('&', $string)) . '&key=' . self::$secretkey;
//        return md5($sign);
//    }

    /**
     * 用户和代理提现公共方法
     * 'type' => 1,  // 类型，0:用户提现 1:代理佣金提现
     */
    public function addWithdrawlLog(Request $request,$type=1) {

        $user_id = $this->getUserId($request->header("token"));
        $user = $this->UserRepository->findByIdUser($user_id);
        $bank_id = $request->bank_id;
        $money = $request->mony;

        $user_bank = $this->UserRepository->getBankByBankId($bank_id);
        if ($user_bank->user_id <> $user_id) {
            $this->_msg = 'The bank card does not match';
            return false;
        }

        // 0:用户提现
        if ($type == 0) {
            $system = $this->systemRepository->getSystem();
            if ((int)$system->multiple > 0) {
                if (((float)$user->total_recharge * (int)$system->multiple) < $money) {
                    $this->_msg = "Your order amount is not enough to complete the withdrawal of {$money} amount, please complete the corresponding order amount before initiating the withdrawal";
                    return false;
                }
            }
            if (((float)$user->cl_betting -  $user->cl_withdrawal) < $money * (int)$system->multiple) {
                $this->_msg = "Your order amount is not enough to complete the withdrawal of {$money} amount, please complete the corresponding order amount before initiating the withdrawal";
                return false;
            }
        }
        $account_holder = $user_bank->account_holder;
        $bank_name = $user_bank->bank_type_id;
        $bank_number = $user_bank->bank_num;
        $ifsc_code = $user_bank->ifsc_code;
        $phone = $user_bank->phone;
        $email = $user_bank->mail;
        $order_no = PayStrategy::onlyosn();
        $data = [
            'user_id' => $user_id,
            'phone' => $phone,
            'nickname' => $user->nickname,
            'money' => $money,
            'create_time' => time(),
            'order_no' => $order_no,
            'pltf_order_no' => '',
            'upi_id' => '',
            'account_holder' => $account_holder,
            'bank_number' => $bank_number,
            'bank_name' => $bank_name,
            'ifsc_code' => $ifsc_code,
            'pay_status' => 0,
            'type' => 0,
            'status' => 0,
            'email' => $email,
            'type' => $type,
        ];
        return $data;
    }

    /**
     * 生成订单号
     */
//    public function onlyosn()
//    {
//        @date_default_timezone_set("Asia/Shanghai");
//        $order_id_main = date('YmdHis') . rand(10000000, 99999999);
//        //订单号码主体长度
//        $order_id_len = strlen($order_id_main);
//        $order_id_sum = 0;
//        for ($i = 0; $i < $order_id_len; $i++) {
//            $order_id_sum += (int)(substr($order_id_main, $i, 1));
//        }
//        //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
//        $osn = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT); //生成唯一订单号
//        return $osn;
//    }
}
