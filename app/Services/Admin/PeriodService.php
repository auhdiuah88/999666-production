<?php


namespace App\Services\Admin;


use App\Repositories\Admin\PeriodRepository;
use App\Services\BaseService;

class PeriodService extends BaseService
{
    private $PeriodRepository;

    public function __construct(PeriodRepository $periodRepository)
    {
        $this->PeriodRepository = $periodRepository;
    }

    public function findAll($page, $limit, $status)
    {
        $list = $this->PeriodRepository->findAll(($page - 1) * $limit, $limit, $status);
        $total = $this->PeriodRepository->countAll($status);
        $this->_data = ["total" => $total, "list" => $list];
    }

    /**
     * 获取最新的数据
     */
    public function getNewest($request)
    {
        $game_id = $request->get("status");
        return $this->PeriodRepository->getNewest($game_id);
    }

    public function searchPeriod($data)
    {
        $page = $data["page"];
        $limit = $data["limit"];
        $offset = ($page - 1) * $limit;
        $list = $this->PeriodRepository->searchPeriod($data, $offset, $limit);
        $total = $this->PeriodRepository->countSearchPeriod($data);
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function findById($id)
    {
        $this->_data = $this->PeriodRepository->findById($id);
    }
}
