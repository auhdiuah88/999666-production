<?php

namespace App\Services\Pay;

class PayContext
{
//    private $strategy = null;

    private $strategList = [];

    // API接口所对应的支付提供商; 需要加入时添加
    public static $pay_provider = [
        '999666.in' => 'leap',

        'unicasino.in' => 'ipay',
        'bb188.in' => 'ipay',
    ];

    public function __construct(
        Ipay $ipay,
        Leap $leap
    )
    {
        // 每种api地址对应的支付厂商
        $this->strategList = [      // 策略工厂
//            '999666.in' => $leap,
//            'unicasino.in' => $ipay,
//            'bb188.in' => $ipay,
            'ipay' => $ipay,
            'leap' => $leap
        ];
    }
    /**
     * 获取具体策略
     * @return PayStrategy
     */
    public function getStrategy(string $strategy)
    {
        if (!isset($this->strategList[$strategy])) {
            return false;
        }
       return $this->strategList[$strategy];
    }
    /**
     *
     * @return PayStrategy
     */
//    public function getStrategy()
//    {
//        if(!$this->strategy) {
//            return false;
//        }
//        return $this->strategy;
//    }
//
//    /**
//     * 充值下单
//     */
//    public function rechargeOrder($pay_type,$money) {
//        return $this->getStrategy()->rechargeOrder($pay_type, $money);
//    }
}














