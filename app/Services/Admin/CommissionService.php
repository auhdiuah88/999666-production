<?php


namespace App\Services\Admin;


use App\Repositories\Admin\CommissionRepository;
use App\Services\BaseService;

class CommissionService extends BaseService
{
    private $CommissionRepository;

    public function __construct(CommissionRepository $commissionRepository)
    {
        $this->CommissionRepository = $commissionRepository;
    }

    public function findAll($page, $limit)
    {
        $list = $this->CommissionRepository->findAll(($page - 1) * $limit, $limit);
        $total = $this->CommissionRepository->countAll();
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function searchCommission($data)
    {
        $offset = ($data["page"] - 1) * $data["limit"];
        $limit = $data["limit"];
        $data = $this->getUserIds($data, "user_id");
        $list = $this->CommissionRepository->searchCommissionLogs($data, $offset, $limit);
        $total = $this->CommissionRepository->countSearchCommissionLogs($data);
        $this->_data = ["total" => $total, "list" => $list];
    }
}
