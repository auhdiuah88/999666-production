<?php


namespace App\Services\Admin;


use App\Repositories\Admin\FirstChargeRepository;
use App\Services\BaseService;

class FirstChargeService extends BaseService
{
    private $FirstChargeRepository;

    public function __construct(FirstChargeRepository $chargeRepository)
    {
        $this->FirstChargeRepository = $chargeRepository;
    }

    public function findAll($page, $limit)
    {
        $list = $this->FirstChargeRepository->findAll(($page - 1) * $limit, $limit);
        $total = $this->FirstChargeRepository->countAll();
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function searchChargeLogs($data)
    {
        $data = $this->getUserIds($data);
        dd($data);
        $list = $this->FirstChargeRepository->searchChargeLogs($data, ($data["page"] - 1) * $data["limit"], $data["limit"]);
        $total = $this->FirstChargeRepository->countSearchChargeLogs($data);
        $this->_data = ["total" => $total, "list" => $list];
    }
}
