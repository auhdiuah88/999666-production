<?php


namespace App\Services\Admin;


use App\Repositories\Admin\ChargeRepository;
use App\Services\BaseService;

class ChargeService extends BaseService
{
    private $ChargeRepository;

    public function __construct(ChargeRepository $chargeRepository)
    {
        $this->ChargeRepository = $chargeRepository;
    }

    public function findAll($page, $limit, $status)
    {
        $list = $this->ChargeRepository->findAll(($page - 1) * $limit, $limit, $status);
        $total = $this->ChargeRepository->countAll($status);
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function searchChargeLogs($data)
    {
        $page = $data["page"];
        $limit = $data["limit"];
        if (array_key_exists("user_phone", $data["conditions"])) {
            $user_phone = $data["conditions"]["user_phone"];
            $data["conditions"]["betting_user_id"] = array_column($this->ChargeRepository->findUserByLike($user_phone), "id");
            unset($data["ops"]["user_phone"]);
            $data["ops"]["betting_user_id"] = "in";
        }
        if (array_key_exists("charge_phone", $data["conditions"])) {
            $charge_phone = $data["conditions"]["charge_phone"];
            $data["conditions"]["charge_user_id"] = array_column($this->ChargeRepository->findUserByLike($charge_phone), "id");
            unset($data["ops"]["charge_phone"]);
            $data["ops"]["charge_user_id"] = "in";
        }
        $total = $this->ChargeRepository->countSearchChargeLogs($data);
        $list = $this->ChargeRepository->searchChargeLogs($data, ($page - 1) * $limit, $limit);
        $this->_data = ["total" => $total, "list" => $list];
    }
}
