<?php


namespace App\Services\Admin;


use App\Repositories\Admin\PeriodRepository;
use App\Repositories\Admin\UserRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class PeriodService extends BaseService
{
    private $PeriodRepository, $UserRepository;

    public function __construct
    (
        PeriodRepository $periodRepository,
        UserRepository $userRepository
    )
    {
        $this->PeriodRepository = $periodRepository;
        $this->UserRepository = $userRepository;
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
        $data = $this->PeriodRepository->findById($id);
        $rel_data = $this->PeriodRepository->findRelById($id, $this->UserRepository->getTestUserIds());
        $data->rel_data = $rel_data;
        $this->_data = $data;
    }

    public function planTaskList()
    {
        $size = request()->input('size',50);
        $game_id = $this->intInput('game_id');
        $where = [
            'is_status' => ['=', 1],
            'end_time' => ['>=', time() + 5 * 60],
            'is_queue' => ['=', 0]
        ];
        if($game_id)
            $where['game_id'] = ['=', $game_id];
        $this->_data = $this->PeriodRepository->planTaskList($where, $size);
    }

    public function getSDList()
    {
        $size = request()->input('size',50);
        $game_id = $this->intInput('game_id');
        $timeMap = request()->input('time',[]);
        $where = [
            'status' => ['=', 0],
            'is_queue' => ['=', 0],
            'is_status' => ['=', 0],
            'prize_number' => ['null','']
        ];
        if($game_id)
            $where['game_id'] = ['=', $game_id];
        if($timeMap){
            $where['end_time'] = ['BETWEEN', $timeMap];
        }else{
            $where['end_time'] = ['>', time() + 20 * 60];
        }
        $this->_data = $this->PeriodRepository->planTaskList($where, $size);
    }

    public function exportTask()
    {
        $size = request()->input('size',50);
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

    public function exportSD()
    {
        $size = request()->input('size',50);
        $page = $this->pageInput();
        $game_id = $this->intInput('game_id');
        $start = request()->input('start_time',[]);
        $end = request()->input('end_time',[]);
        $where = [
            'status' => ['=', 0],
            'is_queue' => ['=', 0],
            'is_status' => ['=', 0],
            'prize_number' => ['null','']
        ];
        if($game_id)
            $where['game_id'] = ['=', $game_id];
        if($start && $end){
            $where['end_time'] = ['BETWEEN', [$start, $end]];
        }else{
            $where['end_time'] = ['>', time() + 20 * 60];
        }
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

    public function SDPrize($period_id, $prize_number)
    {
        $where = [
            'id' => ['=', $period_id],
            'status' => ['=', 0],
            'end_time' => ['>', time() + 10 * 60],
            'is_status' => ['=', 0],
            'is_queue' => ['=', 0],
        ];
        DB::beginTransaction();
        try{
            ##查询期数信息
            $info = $this->PeriodRepository->getPeriodAndLock($where);
            if(!$info){
                throw new \Exception('期数不存在');
            }
            ##修改
            $info->prize_number = $prize_number;
            $info->is_status = 1;
            $info->save();
            DB::commit();
            return true;
        }catch(\Exception $e){
            DB::rollBack();
            return false;
        }
    }

}
