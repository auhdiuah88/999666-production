<?php

namespace App\Repositories\Admin\agent;


use App\Models\Cx_Admin;
use App\Models\Cx_Game_Betting;
use App\Models\Cx_Game_Config;
use App\Models\Cx_Game_Play;
use App\Models\Cx_User;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class AgentBettingRepository extends BaseRepository
{
    private $Cx_Admin, $Cx_Game_Betting, $Cx_Game_Config, $Cx_User, $Cx_Game_Play, $Admin = false;

    public function __construct(Cx_Admin $Cx_Admin,
                                Cx_Game_Betting $game_Betting,
                                Cx_Game_Config $config,
                                Cx_Game_Play $cx_Game_Play,
                                Cx_User $cx_User)
    {
        $this->Cx_Admin = $Cx_Admin;
        $this->Cx_Game_Betting = $game_Betting;
        $this->Cx_Game_Config = $config;
        $this->Cx_Game_Play = $cx_Game_Play;
        $this->Cx_User = $cx_User;
    }

    /**
     * 获取代理下注信息列表
     * @param int $admin_id
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function orders(int $admin_id, int $offset, int $limit)
    {

        $model = $this->getModel();
        $model = $this->whereIn($model, $this->getAdminUserId($admin_id), $offset, $limit);
        $orders = $model->select(["gb.betting_num", "gb.betting_time", "gb.user_id", "gb.game_id", "gb.id", "gb.game_c_x_id", "gb.game_p_id", "gb.money", "gb.odds",
            "gb.service_charge", "gb.type", "gb.status"
        ])
            ->orderByDesc("betting_time")
            ->get()
            ->toArray();
//        foreach ($orders as &$order) {
//            if (isset($order['user']) && $order['user']) {
//                $order['user']['phone'] = $order['user']['phone'] ? hide($order['user']['phone'], 3, 4) : '';
//            }
//
//        }
        return $orders;
    }

    private function getAdminUserId($admin_id)
    {
        return Cx_Admin::find($admin_id)->user_id ?? 0;
    }

    private function setSearchCondition($model)
    {
        $search = request()->all();

        //下注时间
        if (isset($search['betting_num']) && $search['betting_num']) {
            $model->where('betting_num', $search['betting_num']);
        }

        //下单选择
        if (isset($search['selection']) && $search['selection']) {
            $model->whereIn('game_c_x_id', $this->findPlayIds($search["selection"]));
        }

        //用户手机
        if (isset($search['phone']) && $search['phone']) {
            $model->where('user_id', $this->findUserIdByPhone($search["phone"]));
        }
        //游戏种类
        if (isset($search['game_id']) && $search['game_id']) {
            $model->where('game_id', $search['game_id']);
        }

        //游戏期号
        if (isset($search['number']) && $search['number']) {
            $model->whereIn('game_p_id', $this->findNumberId($search["number"]));
        }
        if (isset($search['type']) && $search['type']) {
            $model->where('type', $search['type']);
        }
        //下注时间
        if (isset($search['betting_time_start']) && $search['betting_time_start']) {
            $model->where('betting_time', '>=', $search['betting_time_start']);
        }
        if (isset($search['betting_time_end']) && $search['betting_time_end']) {
            $model->where('betting_time', '<', $search['betting_time_end']);
        }
        return $model;
    }

    /**
     * @param $mode ;
     * @param $admin_user_id
     * @param $offset
     * @param $limit
     * @return mixed
     */
    private function whereIn($model, $admin_user_id, $offset, $limit)
    {
        $subModel = Cx_Game_Betting::query()->from('game_betting as gb');
        //接收查询参数
        $subModel = $this->setSearchCondition($subModel);
        $game_betting_ids = $subModel->whereExists($this->getWhereExists($admin_user_id))
            ->select("gb.id")
            ->orderByDesc("betting_time")
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();
        return $model->whereIn('gb.id', array_column($game_betting_ids, 'id'));
    }

    /**
     * @param int $admin_id
     * @return int
     */
    public function ordersCount(int $admin_id)
    {
        $model = $this->setSearchCondition($this->getModel());
        return $model->whereExists($this->getWhereExists($this->getAdminUserId($admin_id)))
            ->count();
    }

    private function getWhereExists($admin_id)
    {
        return function ($query) use ($admin_id) {
            $query->select(DB::raw('1'))
                ->from('users')
                ->whereRaw('cx_gb.user_id = cx_users.id')
                ->where('invite_relation', 'like', '%-' . $admin_id . '-%');
        };
    }

    public function getModel()
    {
        return $this->Cx_Game_Betting->query()->from('game_betting as gb')->with(["user" => function ($query) {
            $query->select(["id", "phone", "nickname"]);
        }, "game_name" => function ($query) {
            $query->select(["id", "name"]);
        }, "game_play" => function ($query) {
            $query->select(["id", "number", "prize_number"]);
        }, "game_c_x" => function ($query) {
            $query->select(["id", "name"]);
        }]);
    }

    private function findPlayIds($value)
    {
        return array_column($this->Cx_Game_Config->where("name", $value)->get("id")->toArray(), "id");
    }

    private function findUserIdByPhone($phone)
    {
        $user = $this->Cx_User->where("phone", $phone)->first();
        if ($user) {
            return $user->id;
        } else {
            return null;
        }
    }

    private function findNumberId($number)
    {
        return array_column($this->Cx_Game_Play->where("number", $number)->get("id")->toArray(), "id");
    }

    public function statistic($admin_id)
    {
        $data = $this->sumAll(['money' => 'total_betting_money','service_charge' => 'total_service_charge','win_money' => 'total_win_money'], $admin_id);
        $data['betting_count'] = $this->ordersCount($admin_id);
        return $data;
    }

    /**
     * @param array $columnArr
     * @param int $admin_id
     */
    public function sumAll(array $columnArr, int $admin_id)
    {
        $model = $this->Cx_Game_Betting->query()->from('game_betting as gb');
        foreach ($columnArr as $column => $alias) {
            if ($column && $alias) {
                $model->selectRaw("sum({$column}) as {$alias}");
            }
        }
        return $model->whereExists($this->getWhereExists($this->getAdminUserId($admin_id)))->first()->toArray();
    }

}
