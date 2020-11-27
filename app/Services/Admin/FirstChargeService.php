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
        if (array_key_exists("phone", $data["conditions"])) {
            $ids = array_column($this->FirstChargeRepository->findUsers($data["conditions"]["phone"]), "id");
            unset($data["conditions"]["phone"]);
            unset($data["ops"]["phone"]);
            $data["conditions"]["user_id"] = $ids;
            $data["ops"]["user_id"] = "in";
        }
        $list = $this->FirstChargeRepository->searchChargeLogs($data, ($data["page"] - 1) * $data["limit"], $data["limit"]);
        $total = $this->FirstChargeRepository->countSearchChargeLogs($data);
        $this->_data = ["total" => $total, "list" => $list];
    }
}
