<?php


namespace App\Services\Api;


use App\Repositories\Api\UserBalanceRepository;
use App\Services\BaseService;

class UserBalanceService extends BaseService
{

    protected $UserBalanceRepository;

    public function __construct
    (
        UserBalanceRepository $userBalanceRepository
    )
    {
        $this->UserBalanceRepository = $userBalanceRepository;
    }

    public function getAddBalanceLogList()
    {
        $size = request()->get('size',10);
        $this->_data = $this->UserBalanceRepository->getAddBalanceLogList($size);
    }

    public function getReduceBalanceLogList()
    {
        $size = request()->get('size',10);
        $this->_data = $this->UserBalanceRepository->getReduceBalanceLogList($size);
    }

}
