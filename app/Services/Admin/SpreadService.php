<?php


namespace App\Services\Admin;


use App\Repositories\Admin\SpreadRepository;
use App\Services\BaseService;

class SpreadService extends BaseService
{
    private $SpreadRepository;

    public function __construct(SpreadRepository $spreadRepository)
    {
        $this->SpreadRepository = $spreadRepository;
    }

    public function getProfitList($page, $limit, $status)
    {
        $timeMap = [strtotime(date("Y-m-d 00:00:00")), strtotime(date("Y-m-d 23:59:59"))];
        $users = $this->SpreadRepository->findUsers($timeMap);
        $profitList = [];
        $lossList = [];
        foreach ($users as $user) {
            $user->service_charge = $this->SpreadRepository->sumServiceCharge($user->id, $timeMap);
            if ($user->profit_loss < 0) {
                $lossList[] = $user;
            } else {
                $profitList[] = $user;
            }
        }
        if ($status == 0) {
            $list = collect($profitList)->sortByDesc("profit_loss")->slice(($page - 1) * $limit, $limit)->values();
            $total = collect($profitList)->count();
            $this->_data = ["total" => $total, "list" => $list];
        } else {
            $list = collect($lossList)->sortByDesc("profit_loss")->slice(($page - 1) * $limit, $limit)->values();
            $total = collect($lossList)->count();
            $this->_data = ["total" => $total, "list" => $list];
        }
    }
}
