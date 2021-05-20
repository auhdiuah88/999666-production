<?php


namespace App\Services\Api;

use App\Common\Common;
use App\Dictionary\SettingDic;
use App\Repositories\Api\PlatformBankCardsRepository;
use App\Repositories\Api\RechargeRepository;
use App\Repositories\Api\SettingRepository;
use App\Repositories\Api\UserRepository;
use App\Repositories\Api\WithdrawalRepository;
use App\Services\Library\Auth;
use App\Services\Library\Netease\IM;
use App\Services\Library\Netease\SMS;
use App\Services\Library\Upload;
use App\Services\Pay\PayContext;
use App\Services\PayService;
use App\Services\RequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RechargeService extends PayService
{
    private $userRepository, $rechargeRepository, $withdrawalRepository, $requestService, $payContext, $SettingRepository, $PlatformBankCardsRepository;

    public function __construct
    (
        UserRepository $userRepository,
        RechargeRepository $rechargeRepository,
        WithdrawalRepository $withdrawalRepository,
        RequestService $requestService,
        PayContext $payContext,
        SettingRepository $settingRepository,
        PlatformBankCardsRepository $platformBankCardsRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->rechargeRepository = $rechargeRepository;
        $this->withdrawalRepository = $withdrawalRepository;
        $this->requestService = $requestService;
        $this->payContext = $payContext;
        $this->SettingRepository = $settingRepository;
        $this->PlatformBankCardsRepository = $platformBankCardsRepository;
    }

    public $rtn = '';

    /***
     * 充值下单接口-JSON封装请求
     * GET方式  返回支付URL链接
     */
    public function rechargeOrder(Request $request)
    {
        $user_id = $this->getUserId($request->header("token"));
        $user = $this->userRepository->findByIdUser($user_id);

        $pay_type = $request->pay_type;
        $money = $request->money;

        $host = $request->getHost();    // 根据api接口host判断是来源于哪个客户；用什么支付方式  //  $host = "api.999666.in"; 变成 999666.in
        if (count(explode('.', $host)) == 3) {
            $host = substr(strstr($host, '.'), 1);
        }

//        $payProvide = PayContext::$pay_provider[$host];
        $payProvide = $pay_type;   // 根据传入的类型（支付公司类型选折策略）
        $strategyClass = $this->payContext->getStrategy($payProvide);  // 获取支付商户类
        if (!$strategyClass) {
            $this->_msg = 'can not find pay mode';
            return false;
        }
        if(in_array($payProvide, $strategyClass->vnPayArray)){
            $strategyClass->rechargeType = $request->charge_type;
        }
        $result = $strategyClass->rechargeOrder($pay_type, $money);
        if (!$result) {
            $this->_msg = $strategyClass->_msg;
            return false;
        }
        // 添加充值记录
        $order_no = $result['out_trade_no'];
        $pay_type = $result['pay_type'];
        $pltf_order_id = $result['pltf_order_id'];
        $native_url = $result['native_url'];
        $verify_money = $result['verify_money'];
        $match_code = $result['match_code'];
        $result['is_post'] = isset($result['is_post']) ? $result['is_post'] : 0;
        $result['params'] = isset($result['params']) ? $result['params'] : [];
        $this->rechargeRepository->addRechargeLog($user, $money, $order_no, $pay_type, $pltf_order_id, $native_url, $verify_money, $match_code);
        return $result;
    }

    /**
     * 充值订单查询
     */
//    public function orderQuery($order_no, $pltf_order_id = '')
//    {
//        $params = [
//            'mch_id' => self::$merchantID,
//            'out_order_sn' => $order_no,
//            'time' => time(),
//        ];
//        $params['sign'] = self::generateSign($params);
//        return $this->requestService->postFormData(self::$url . '/order/query', $params);
//    }

    /**
     *  充值回调
     *
     * 请求参数    参数名    数据类型    可空    说明
     * 商户单号        sh_order    string    否    商户系统的业务单号
     * 平台单号        pt_order    string    否    支付平台的订单号
     * 订单金额        money    float    否    与支付提交的金额一致
     * 支付完成时间    time    int    否    系统时间戳UTC秒（10位）
     * 订单状态        state    int    否    订单状态
     * 0 已提交       1 已接单
     * 2 超时补单     3 订单失败
     * 4 交易完成     5 未接单
     * 商品描述    goods_desc    string    否    订单描述或备注信息
     * 签名        sign    string    否    见签名算法
     *
     * 收到回调处理完业务之后请输出固定的 success
     */
    public function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('rechargeCallback', $request->input());
        $payProvide = $request->input('backup_type', '');
        if(!$payProvide)
            $payProvide = $request->input('type', '');
        if (!$payProvide) {
            $this->_msg = 'can not find pay Provide';
            return false;
        }
        $strategyClass = $this->payContext->getStrategy($payProvide);  // 获取支付提供商类
        if (!$strategyClass) {
            $this->_msg = 'can not find pay mode';
            return false;
        }
        if (!$where = $strategyClass->rechargeCallback($request)) {
            $this->_msg = $strategyClass->_msg;
            return false;
        }
        $this->rtn = $strategyClass->rechargeRtn;
        if(!is_array($where)){
            return true;
        }
        if(isset($where['pltf_order_id'])){
            $pltf_order_id = $where['pltf_order_id'];
            unset($where['pltf_order_id']);
        }
        $requestData = $request->all();
        if($strategyClass->amount > 0){
            $money = $strategyClass->amount;
        }else{
            ##ipay=>money  mtbpay=>pay_amount amout=>winpay leap=>money in8pay=>amount/100  sevenpay=>payamount
            if(!empty($strategyClass->amountFiled)){
                if($payProvide == 'YJpay'){
                    $money = $requestData['data'][$strategyClass->amountFiled];
                }else{
                    $money = $requestData[$strategyClass->amountFiled];
                }
            }else{
                $money = isset($requestData['money']) ? $requestData['money'] : (isset($requestData['pay_amount']) ? $requestData['pay_amount'] : (isset($requestData['amt'])?$requestData['amt']: (isset($requestData['payamount'])?$requestData['payamount']:$requestData['amount'])));
            }

            if($payProvide == 'in8pay' || $payProvide == 'YJpay'){ //返回的是分做单位的
                $money = $money / 100;
            }
        }

        // 下面的方法相同
        $rechargeLog = $this->rechargeRepository->getRechargeInfoByCondition($where);
        if (!$rechargeLog) {
            $this->_msg = '找不到此订单';
            return false;
        }

        if ($rechargeLog->status == 2) {
            $this->_msg = '已成功充值,无需再回调';
            return false;
        }

        ##判断充值金额
//        if($rechargeLog->money > $money){
//            $this->_msg = '充值金额小于订单金额';
//            return false;
//        }

        DB::beginTransaction();
        try {
            $user = $this->userRepository->findByIdUser($rechargeLog->user_id);

            // 是否第一次充值
            if ((int)$user->is_first_recharge == 0 && $money >= config('site.invite_recharge_valid_money',200)) {
//                $user->is_first_recharge = 1;
                $referrensUser = $this->userRepository->findByIdUser($user->two_recommend_id);  // 给推荐我注册的人，推荐充值数加1
                if ($referrensUser) {
                    $referrensUser->rec_ok_count += 1;
                    $referrensUser->save();
                }
            }

            // 记录充值成功余额变动
            $this->userRepository->updateRechargeBalance($user, $money);

            // 更新充值成功记录的状态
            $rechargeLog->arrive_time = time();
            $rechargeLog->arrive_money = $money;
            if(isset($pltf_order_id)){
                $rechargeLog->pltf_order_id = $pltf_order_id;
            }
            $rechargeLog->status = 2;
            $rechargeLog->save();

            ##判断返利
            $config = $this->SettingRepository->getSettingValueByKey(SettingDic::key('RECHARGE_REBATE'));
            $this->userRepository->rebate($rechargeLog, $config, $money);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->_msg = $e->getMessage();
            Log::channel('mytest')->info('rechargeCallback_err', ['err'=>$e->getMessage(), 'line'=>$e->getLine()]);
            return false;
        }
        return true;
    }

    /**
     * 充值记录
     */
    public function rechargeLog($request)
    {
        $userInfo = $request->get('userInfo');
        $uid = $userInfo['id'];
        return $this->rechargeRepository->getRechargeLogs($uid, $request->status, $request->limit, $request->page);
    }

    public function rechargeConfirm($request)
    {
        DB::beginTransaction();
        $order_no = $request->input('order_no');
        try {
            $rechargeLog = $this->rechargeRepository->findRechargeLogByOrderNo($order_no);
            //修改订单记录
            $this->rechargeRepository->rechargeConfirm($order_no);
            $user = $this->userRepository->findByIdUser($rechargeLog->user_id);
            //修改用户余额 添加余额变动记录
            $this->userRepository->updateRechargeBalance($user, $rechargeLog->money);
            DB::commit();
        } catch (\Exception $e) {
            $this->_msg = 'error';
            DB::rollBack();
            return false;
        }
        return [];
    }

    public function getConfig()
    {
        $recharge_type = config('pay.recharge',[]);
        $setting_value = $this->SettingRepository->getRecharge();
        $config = [];
        foreach($recharge_type as $key){
            if(isset($setting_value[$key])){
                $item = $setting_value[$key];
                if(isset($item['status']) && $item['status'] == 1)
                    $config[] = [
                        'company' => $key,
                        'max_money' => $item['limit']['max'],
                        'min_money' => $item['limit']['min'],
                        'money' => $item['btn'],
                    ];
            }
        }
        $this->_data = $config;
    }

    public function platBankCards()
    {
        $data = $this->PlatformBankCardsRepository->lists();
        if($data->isEmpty()){
            $this->_code = 403;
            $this->_msg = 'No bank card information';
        }else{
            $this->_data = $data;
        }
    }

    public function requestDirectRecharge()
    {
        ##检查用户未审核的申请
        if($this->rechargeRepository->requestDirectRechargeNum(['user_id'=>['=', request()->get('userInfo')['id']], 'status'=>['=', 0]]) >= 5)
        {
            $this->_code = 402;
            $this->_msg = 'There are currently 5 pending applications, Please try again later';
            return;
        }
        if($this->rechargeRepository->requestDirectRecharge(array_merge(request()->only(['bank_card_id', 'money', 'remark']), ['user_id' => request()->get('userInfo')['id']])) === false)
        {
            $this->_code = 402;
            $this->_msg = 'Submit fail';
        }else{
            $this->_msg = 'Submit success';
        }
    }

    public function requestDirectRechargeLogs()
    {
        $where = [
            'user_id' => ['=', request()->get('userInfo')['id']],
            'status' => ['=', $this->intInput('status')]
        ];
        $this->_data = $this->rechargeRepository->requestDirectRechargeLogs($where, $this->sizeInput());
    }

}

