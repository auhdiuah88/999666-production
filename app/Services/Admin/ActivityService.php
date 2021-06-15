<?php


namespace App\Services\Admin;


use App\Repositories\Admin\ActivityRepository;
use App\Services\BaseService;

class ActivityService extends BaseService
{

    protected $ActivityRepository;

    public function __construct
    (
        ActivityRepository $activityRepository
    )
    {
        $this->ActivityRepository = $activityRepository;
    }

    public function getSignProduct()
    {
        $this->_data = $this->ActivityRepository->getSignProduct();
    }

    public function signProductEdit(): bool
    {
        $data = [
            'id' => $this->intInput('id'),
            'name' => $this->strInput('name'),
            'status' => $this->intInput('status'),
            'amount' => $this->floatInput('amount'),
            'daily_rebate' => $this->floatInput('daily_rebate'),
            'payback_cycle' => $this->intInput('payback_cycle'),
            'rebate_ratio' => $this->floatInput('rebate_ratio'),
            'stock' => $this->intInput('stock'),
        ];
        $data['receive_amount'] = bcmul($data['daily_rebate'], $data['payback_cycle']);
        $data['profit'] = bcsub($data['receive_amount'], $data['amount']);
        $res = $this->ActivityRepository->signProductEdit($data);
        if($res === false){
            $this->_code = 302;
            $this->_msg = '操作失败';
            return false;
        }
        return true;
    }

    public function getRedEnvelopeTask()
    {
        $this->_data = $this->ActivityRepository->getRedEnvelopeTask();
    }

    public function redEnvelopeTaskEdit(): bool
    {
        $data = [
            'id' => $this->intInput('id'),
            'name' => $this->strInput('name'),
            'status' => $this->intInput('status'),
            'value' => $this->intInput('value'),
            'reward' => $this->floatInput('reward'),
            'expire' => strtotime($this->strInput('expire')),
        ];
        $res = $this->ActivityRepository->redEnvelopeTaskEdit($data);
        if($res === false){
            $this->_code = 302;
            $this->_msg = '操作失败';
            return false;
        }
        return true;
    }

}
