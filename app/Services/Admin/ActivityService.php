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
            'daily_rebate' => $this->floatInput('amount'),
            'payback_cycle' => $this->intInput('payback_cycle'),
            'rebate_ratio' => $this->floatInput('rebate_ratio'),
            'stock' => $this->intInput('stock'),
        ];
        $res = $this->ActivityRepository->signProductEdit($data);
        if($res === false){
            $this->_code = 302;
            $this->_msg = '操作失败';
            return false;
        }
        return true;
    }

}
