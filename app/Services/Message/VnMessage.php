<?php


namespace App\Services\Message;


use Illuminate\Support\Facades\Log;

class VnMessage extends MessageStrategy
{

    private $desc = "越南短信";

    private $account = 'cs_holmer';

    private $password = '4O14kov8';

    private $api = "http://sms.skylinelabs.cc:20003/sendsmsV2";

    private $codeDesc = [
        '0' => '获取成功',
        '-1' => '认证错误',
        '-2' => 'Ip访问受限',
        '-3' => '短信内容含有敏感字符',
        '-4' => '短信内容为空',
        '-5' => '短信内容过长',
        '-6' => '不是模板的短信',
        '-7' => '号码个数过多',
        '-8' => '号码为空',
        '-9' => '号码异常',
        '-10' => '该通道余额不足，不能满足本次发送',
        '-11' => '定时时间格式不对',
        '-12' => '由于平台的原因，批量提交出错，请与管理员联系',
        '-13' => '用户被锁定',
    ];

    function sendRegisterCode($phone): array
    {
        $code = $this->makeCode();
        $content = $this->makeContent($this->registerMessage, $code);
        $res = $this->sendSms($phone, $content);
        if ($res) {
            return ["code" => $this->_code, "obj" => $code];
        }
        return ["code" => $this->_code];
    }

    protected function makeSign($time): string
    {
        return strtolower(md5($this->account . $this->password . $time));
    }

    protected function sendSms($phone, $content): bool
    {
        $phone = "0084" . $phone;
        $params = [
            'account' => $this->account,
            'datetime' => date('YmdHis'),
            'numbers' => (string)$phone,
            'content' => $content
        ];
        $params['sign'] = $this->makeSign($params['datetime']);
        $res = $this->requestService->get($this->api,$params,[
            "content-type" => "application/x-www-form-urlencoded",
        ]);
        if(!$res)
            return $this->setError(402,'Failed to submit');
        if($res['status'] != 0){
            Log::channel('kidebug')->info('越南短信',[$res]);
            return $this->setError(402,'Send Sms Failed, Error Code ' . $res['status']);
        }
        if($res['success'] >= 1)return true;
        return $this->setError(200,'Send Sms Failed');
    }
}
