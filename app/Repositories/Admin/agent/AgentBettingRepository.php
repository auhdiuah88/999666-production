<?php

namespace App\Repositories\Admin\agent;


use App\Models\Cx_Admin;
use App\Models\Cx_Game_Betting;
use App\Models\Cx_User;
use Illuminate\Support\Facades\DB;

class AgentBettingRepository
{
    private $Cx_Admin, $Cx_Game_Betting;
    /**
     * @var Cx_User $Cx_User
     */
    private $Cx_User;

    public function __construct(Cx_Admin $Cx_Admin, Cx_User $Cx_User, Cx_Game_Betting $Cx_Game_Betting)
    {
        $this->Cx_Admin = $Cx_Admin;
        $this->Cx_Game_Betting = $Cx_Game_Betting;
        $this->Cx_User = $Cx_User;
    }

//    public function findAll($offset, $limit)
//    {
//        $this->Cx_Admin->find();
//        $users = $this->Cx_User->all();
//        dd($bettings);
//    }

    /**
     * 获取代理的下注信息列表
     * @param $admin_id
     */
    public function orders(int $admin_id)
    {
        var_dump($admin_id);

        DB::connection()->enableQueryLog();
        $user = $this->Cx_User->where('invite_relation', 'like', '%-' . $admin_id . '-%')->get();
        $res = DB::getQueryLog();
        var_dump($res);
        var_dump($user);
        die;
    }

    public function ordersCount()
    {
    }

}
