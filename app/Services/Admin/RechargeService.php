<?php


namespace App\Services\Admin;


use App\Repositories\Admin\RechargeRepository;
use App\Repositories\Admin\UserRepository;
use App\Services\BaseService;

class RechargeService extends BaseService
{
    private $RechargeRepository, $UserRepository;

    public function __construct(RechargeRepository $rechargeRepository, UserRepository $userRepository)
    {
        $this->RechargeRepository = $rechargeRepository;
        $this->UserRepository = $userRepository;
    }

    public function findAll($page, $limit, $status)
    {
        $list = $this->RechargeRepository->findAll(($page - 1) * $limit, $limit, $status);
        $total = $this->RechargeRepository->countAll($status);
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function searchRechargeLogs($data)
    {
        if(isset($data['conditions']['status']) && $data['conditions']['status'] == 0)unset($data['conditions']['status']);
        if(isset($data['conditions']['agent']))unset($data['conditions']['agent']);
        $data = $this->getUserIds($data, "user_id");
        $list = $this->RechargeRepository->searchChargeLogs($data, ($data["page"] - 1) * $data["limit"], $data["limit"]);
        $total = $this->RechargeRepository->countSearchChargeLogs($data);
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function getUser($id)
    {
        $this->_data = $this->UserRepository->findById($id);
    }

    public function getNewest()
    {
        $result = $this->RechargeRepository->getNewest();
        if (!$result) {
            return ['create_at' => 0, 'id' => 0];
        }
        return $result;
    }
}
