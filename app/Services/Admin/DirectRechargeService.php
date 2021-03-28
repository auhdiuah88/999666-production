<?php


namespace App\Services\Admin;


use App\Dictionary\SettingDic;
use App\Repositories\Admin\DirectRechargeRepository;
use App\Repositories\Admin\SettingRepository;
use App\Repositories\Admin\UserRepository;
use App\Services\BaseService;
use App\Repositories\Api\UserRepository as ApiUserRepository;
use Illuminate\Support\Facades\DB;

class DirectRechargeService extends BaseService
{

    protected $DirectRechargeRepository, $UserRepository, $SettingRepository, $ApiUserRepository;

    public function __construct
    (
        DirectRechargeRepository $directRechargeRepository,
        UserRepository $userRepository,
        SettingRepository $settingRepository,
        ApiUserRepository $apiUserRepository
    )
    {
        $this->DirectRechargeRepository = $directRechargeRepository;
        $this->UserRepository = $userRepository;
        $this->SettingRepository = $settingRepository;
        $this->ApiUserRepository = $apiUserRepository;
    }

    public function lists()
    {
        $this->_data = $this->DirectRechargeRepository->lists($this->setListsWhere(), $this->sizeInput());
    }

    protected function setListsWhere(): array
    {
        $where = [];
        $order_no = $this->strInput('order_no');
        if($order_no)
            $where['order_no'] = ['=', $order_no];
        $user_id = $this->intInput('user_id');
        if($user_id)
            $where['user_id'] = ['=', $user_id];
        $status = $this->intInput('status',-1);
        if($status != -1)
            $where['status'] = ['=', $status];
        $phone = $this->strInput('phone');
        if($phone)
            $where['phone'] = $phone;
        return $where;
    }

    public function exam(): bool
    {
        $status = $this->intInput('status');
        $id = $this->intInput('id');
        $requestCharge = $this->check($id);
        if(!$requestCharge)return false;

        if($status == 1)
        {
            ##审核金额
            $real_money = $this->floatInput('real_money');
            if($real_money <= 0)
            {
                return $this->returnWithErr("请填确认审核金额");
            }

            $user = $this->UserRepository->findById($requestCharge->user_id);

            $update = [
                'id' => $id,
                'real_money' => $real_money,
            ];
            DB::beginTransaction();
            try {
                // 是否第一次充值
                if ((int)$user->is_first_recharge == 0 && $real_money >= 200) {
                    $referrensUser = $this->ApiUserRepository->findByIdUser($user->two_recommend_id);  // 给推荐我注册的人，推荐充值数加1
                    if ($referrensUser) {
                        $referrensUser->rec_ok_count += 1;
                        $referrensUser->save();
                    }
                }

                // 记录充值成功余额变动
                $this->ApiUserRepository->updateRechargeBalance2($user, $real_money);
                // 更新充值成功记录的状态
                $this->DirectRechargeRepository->pass($update);

                ##判断返利
                $config = $this->SettingRepository->getSettingValueByKey(SettingDic::key('RECHARGE_REBATE'));
                $this->ApiUserRepository->rebate($requestCharge, $config, $real_money);

                DB::commit();
                return true;
            }catch(\Exception $e) {
                DB::rollBack();
                return $this->returnWithErr($e->getMessage());
            }
        }elseif($status == 2)
        {
            ##驳回理由
            $message = $this->strInput('message');
            if(empty($message))
            {
                return $this->returnWithErr("请填写驳回理由");
            }
            $update = [
                'id' => $id,
                'message' => $message
            ];
            $this->DirectRechargeRepository->refuse($update);
            return true;
        }
        return false;
    }

    protected function check($id)
    {
        $requestCharge = $this->DirectRechargeRepository->getInfoById($id);
        if(!$requestCharge)
        {
            return $this->returnWithErr("订单不存在");
        }
        if($requestCharge->status != 0)
        {
            return $this->returnWithErr("订单状态非待审核,不可处理");
        }
        return $requestCharge;
    }

    protected function returnWithErr($msg='', $code=402): bool
    {
        $this->_code = $code;
        $this->_msg = $msg;
        return false;
    }

}
