<?php

namespace App\Services\Pay;

use App\Services\Pay\BR\JunHePay;
use App\Services\Pay\INDIA\EKPay;

class PayContext
{
//    private $strategy = null;

    private $strategList = [];

    // API接口所对应的支付提供商; 需要加入时添加
    public static $pay_provider = [
        '999666.in' => 'leap',
//        '999666.in' => 'winpay',
        'unicasino.in' => 'MTBpay',
        'bb188.in' => 'ipay',
        'my-sky-shop.com' => 'MTBpay'
    ];

    public function __construct(
        Ipay $ipay,
        Leap $leap,
        Winpay $winpay,
        MTBpay $mtbpay,
        Rspay $rspay,
        In8pay $in8pay,
        Richpay $richpay,
        Inpays $inpays,
        Payq $payq,
        HuiZhong $huiZhong,
        VNMTBpay $VNMTBpay,
        Sevenpay $sevenpay,
        Sepropay $sepropay,
        Matthew $matthew,
        Pradapay $pradapay,
        YJpay $YJpay,
        Yeahpay $yeahpay,
        SevenIndiaPay $sevenIndiaPay,
        PrinceVnPay $princeVnPay,
        Fypay $fypay,
        Wowpay $wowpay,
        HXpay $HXpay,
        Four2pay $four2pay,
        BRHXpay $BRHXpay,
        GlobalPay $globalPay,
        TongLinkPay $tongLinkPay,
        YLpay $YLpay,
        CloudPay $cloudPay,
        DDpay $DDpay,
        Gspay $gspay,
        JunHePay $junHePay,
        EKPay $EKPay
    )
    {
        // 每种api地址对应的支付公司
        $this->strategList = [  // 策略工厂
//            '999666.in' => $leap,
//            'unicasino.in' => $ipay,
//            'bb188.in' => $ipay,

            'ipay' => $ipay,
            'leap' => $leap,
            'winpay' => $winpay,
            'MTBpay' => $mtbpay,
            'rspay' => $rspay,
            'in8pay' => $in8pay,
            'richpay' => $richpay,
            'inpays' => $inpays,
            'payq' => $payq,
            'huizhong' => $huiZhong,  //mtb
            'vnpay' => $VNMTBpay,  //越南mtb
            '777pay' => $sevenpay,  //777pay 越南
            'sepro' => $sepropay,
            'matthew' => $matthew, //coo7
            'pradapay' => $pradapay,
            'YJpay' => $YJpay,
            'Yeahpay' => $yeahpay,
            '77pay' => $sevenIndiaPay, // 印度777pay
            'princepay' => $princeVnPay, // 越南王子支付
            'fypay' => $fypay,   //越南凤扬支付
            'WOWpay' => $wowpay,  //印度wow支付
            'HXpay' => $HXpay,  //印度hxpay
            'Four2' => $four2pay,  //印度four2
            'BRHX' => $BRHXpay,  //巴西hx
            'globalpay' => $globalPay,  //印度-》前惠众 换了网关
            'TongLink' => $tongLinkPay,  //巴西tonglink
            'ylpay' => $YLpay,  //印度 类似sepropay
            'cloudpay' => $cloudPay,  //印度云支付
            'ddpay' => $DDpay,  //印度DDPAY
            'gspay' => $gspay,  //印度gapayment
            'junhe' => $junHePay,
            'ekpay' => $EKPay,  //印度ek
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














