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

    public function planTaskList()
    {
        $size = $this->sizeInput();
        $game_id = $this->intInput('game_id');
        $where = [
            'is_status' => ['=', 1],
            'end_time' => ['<', time() - 5 * 60],
            'is_queue' => ['=', 0]
        ];
        if($game_id)
            $where['game_id'] = ['=', $game_id];
        $this->_data = $this->PeriodRepository->planTaskList($where, $size);
    }

    public function exportTask()
    {
        $size = $this->sizeInput();
        $page = $this->pageInput();
        $game_id = $this->intInput('game_id');
        $where = [
            'is_status' => ['=', 1],
            'end_time' => ['<', time() - 5 * 60],
            'is_queue' => ['=', 0]
        ];
        if($game_id)
            $where['game_id'] = ['=', $game_id];
        $data = $this->PeriodRepository->exportTask($where, $size, $page);
        $result = array_merge([[
            '期数ID',
            '期数',
            '开始时间',
            '开奖时间',
            '本期结果',
            '游戏类型',
        ]], $data);
        $this->_data = $result;
    }

}
