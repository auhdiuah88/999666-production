<?php


namespace App\Services\Admin;


use App\Repositories\Admin\WagerRepository;
use App\Services\BaseService;

class WagerService extends BaseService
{
    private $WagerRepository;

    public function __construct(WagerRepository $wagerRepository)
    {
        $this->WagerRepository = $wagerRepository;
    }

    public function findAll($page, $limit)
    {
        $list = $this->WagerRepository->findAll(($page - 1) * $limit, $limit);
        $total = $this->WagerRepository->countAll();
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function searchWager($data)
    {
        $data = $this->getUserIds($data);
        if (array_key_exists("betting_time", $data["conditions"])) {
            $betting = $data["conditions"]["betting_time"];
            unset($data["conditions"]["betting_time"]);
            unset($data["ops"]["betting_time"]);
            $str = "-" . $betting . " days";
            $betting_time = strtotime($str);
        } else {
            $betting_time = time();
        }
        $list = $this->WagerRepository->searchWager($data, $betting_time, ($data["page"] - 1) * $data["limit"], $data["limit"]);
        $total = $this->WagerRepository->countWager($data);
        $this->_data = ["total" => $total, "list" => $list];
    }
}
