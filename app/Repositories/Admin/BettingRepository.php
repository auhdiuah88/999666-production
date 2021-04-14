<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Game;
use App\Models\Cx_Game_Betting;
use App\Models\Cx_Game_Config;
use App\Models\Cx_Game_Play;
use App\Models\Cx_User;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class BettingRepository extends BaseRepository
{
    private $Cx_Game_Betting, $Cx_Game_Config, $Cx_Game_Play, $Cx_User;
    /**
     * @var Cx_Game
     */
    private $cx_Game;

    public function __construct(
        Cx_Game_Betting $game_Betting,
        Cx_Game_Config $config,
        Cx_Game_Play $cx_Game_Play,
        Cx_User $cx_User,
        Cx_Game $cx_Game
    )
    {
        $this->Cx_Game_Betting = $game_Betting;
        $this->Cx_Game_Config = $config;
        $this->Cx_Game_Play = $cx_Game_Play;
        $this->Cx_User = $cx_User;
        $this->cx_Game = $cx_Game;
    }

    public function findAll($offset, $limit)
    {
        return $this->getModel()->orderByDesc("betting_time")->offset($offset)->limit($limit)->get()->setAppends(['win_lose_money'])->toArray();
    }

    /**
     * 获取最新数据
     */
    public function getNewest()
    {
        return $this->getModel()->orderByDesc("betting_time")->limit(10)->get()->setAppends(['win_lose_money']);
    }

    public function countAll($data)
    {
        return $this->whereCondition($data, $this->Cx_Game_Betting)->count("id");
    }

    public function sumAll($data,$column)
    {
        return $this->whereCondition($data, $this->Cx_Game_Betting)->sum($column);
    }

    public function searchBettingLogs($data, $offset, $limit)
    {
        return $this->whereCondition($data, $this->getModel())->orderByDesc("betting_time")->offset($offset)->limit($limit)->get()->setAppends(['win_lose_money'])->toArray();
    }

    public function countSearchBettingLogs($data)
    {
        return $this->whereCondition($data, $this->getModel())->count("id");
    }

    public function findPlayIds($value)
    {
        return array_column($this->Cx_Game_Config->where("name", $value)->get("id")->toArray(), "id");
    }

    public function findNumberId($number)
    {
        return array_column($this->Cx_Game_Play->where("number", $number)->get("id")->toArray(), "id");
    }

    public function findUserId($phone)
    {
        $user = $this->Cx_User->where("phone", $phone)->first();
        if (empty($user)) {
            return null;
        } else {
            return $user->id;
        }
    }

    public function getModel()
    {
        return $this->Cx_Game_Betting->with(["user" => function ($query) {
            $query->select(["id", "phone", "nickname"]);
        }, "game_name" => function ($query) {
            $query->select(["id", "name"]);
        }, "game_play" => function ($query) {
            $query->select(["id", "number", "prize_number"]);
        }, "game_c_x" => function ($query) {
            $query->select(["id", "name"]);
        }]);
    }

    public function statistics($type)
    {
        $userInfo = request()->get('userInfo');
        $uid = $userInfo['id'];
        $data = [
            'count_money' => 0,
            'count_win_money' => 0,
            'count_win_lose_money' => 0,
        ];
        $model = $this->Cx_Game_Betting
            ->rightJoin('game', 'game_id', '=', 'game.id')
            ->where($this->rangeTime($type))
            ->addSelect(DB::raw('sum(money) as t_money, sum(win_money) as t_win_money, game_id, sum(win_money) - sum(money) as win_lose'))
            ->groupBy('game_id')
            ->where('user_id', $uid);
        $data['list'] = $this->cx_Game
            ->select(['game.name', 't_money', 'if(t_win_money, t_win_money, 0)', 'win_lose'])
            ->select(DB::raw('name, if(t_money, t_money, 0.00) as t_money, if(t_win_money, t_win_money, 0.00) as t_win_money, if(win_lose, win_lose, 0.00) as win_lose'))
            ->leftJoinSub($model, 'gb', function ($join){
            $join->on('game.id', '=', 'gb.game_id');
            })
            ->get()
            ->toArray();
        if ($data['list']) {
            $data['count_money'] = sprintf('%01.2f', array_sum(array_column($data['list'] , 't_money')));
            $data['count_win_money'] = sprintf('%01.2f', array_sum(array_column($data['list'] , 't_win_money')));
            $data['count_win_lose_money'] = sprintf('%01.2f', array_sum(array_column($data['list'] , 'win_lose')));
        }
        return $data;
    }

    /**
     * @param $type 1 今日 2 昨日 3 这个月
     * @return array
     */
    private function rangeTime($type)
    {
        $where = [];
        if ($type == 1) {
            $where[] = [
                'betting_time', '>', mktime(0, 0, 0, date('m'), date('d'), date('Y'))
            ];
        } elseif ($type == 2) {
            $where[] = ['betting_time', '<', mktime(0, 0, 0, date('m'), date('d'), date('Y'))];
            $where[] = ['betting_time', '>', mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'))];

        } else {
            $where[] = ['betting_time', '>', mktime(0, 0, 0, date('m'), 1 , date('Y'))];
        }
        return $where;
    }

    /**
     * 下注提醒用户列表
     * @param $where
     * @param $size
     * @return mixed
     */
    public function noticeList($where, $size)
    {
        return makeModel($where, $this->Cx_User)
            ->select(['id', 'phone', 'balance', 'cl_withdrawal', 'is_first_recharge', 'cl_betting', 'cl_betting_total'])
            ->orderByDesc('cl_betting')
            ->paginate($size);
    }

    /**
     * 下厨提醒用户投注列表
     * @param $sort
     * @param $size
     * @return mixed
     */
    public function noticeBettingList2($sort, $size)
    {
        $sortArr = [
            1 => 'money',
            2 => 'betting_time'
        ];
        $sub = $this->Cx_User->where(['is_betting_notice'=>['=', 1]])->select(['id']);
        return $this->Cx_Game_Betting
            ->joinSub($sub, 'user', function($join){
                $join->on('game_betting.user_id', '=', 'user.id');
            })
            ->select(['user_id','betting_num', 'game_betting.id as betting_id', 'game_id', 'game_p_id', 'game_c_x_id', 'money', 'odds', 'win_money', 'betting_time', 'settlement_time', 'status', 'type', 'service_charge'])
            ->with(
                [
                    'game_c_x' => function($query){
                        $query->select(['id', 'name']);
                    },
                    'game_name' => function($query)
                    {
                        $query->select(['id', 'name']);
                    },
                    'game_play' => function($query){
                        $query->select(['id', 'number', 'prize_number', 'is_status']);
                    }
                ]
            )
            ->orderByDesc($sortArr[$sort])
            ->paginate($size);
    }

    /**
     * 下厨提醒用户投注列表
     * @param $where
     * @param $sort
     * @param $size
     * @return mixed
     */
    public function noticeBettingList($where, $sort, $size)
    {
        $sortArr = [
            1 => 'money',
            2 => 'betting_time'
        ];
        return makeModel($where, $this->Cx_Game_Betting)
            ->select(['user_id','betting_num', 'game_betting.id as betting_id', 'game_id', 'game_p_id', 'game_c_x_id', 'money', 'odds', 'win_money', 'betting_time', 'settlement_time', 'status', 'type', 'service_charge', 'betting_time as betting_time_format', 'settlement_time as settlement_time_format'])
            ->with(
                [
                    'game_c_x' => function($query){
                        $query->select(['id', 'name']);
                    },
                    'game_name' => function($query)
                    {
                        $query->select(['id', 'name']);
                    },
                    'game_play' => function($query){
                        $query->select(['id', 'number', 'prize_number', 'is_status']);
                    }
                ]
            )
            ->orderByDesc($sortArr[$sort])
            ->paginate($size);
    }
}
