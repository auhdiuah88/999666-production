<?php


namespace App\Repositories\Game;


use App\Common\Common;
use App\Common\RecursiveCommon;

use Illuminate\Support\Facades\Cache;
use App\Models\Cx_Game_Play;
use App\Models\Cx_Game;
use App\Models\Cx_User;
use App\Models\Cx_Game_Betting;
use App\Models\Cx_Date_Prize;
use App\Models\Cx_User_Balance_Logs;
use App\Models\Cx_User_Recharge_Logs;
use App\Repositories\Api\UserRepository;
use App\Mongodb;
use Illuminate\Support\Facades\DB;
use App\Models\Cx_Game_Config;
use App\Models\Cx_System;
use Illuminate\Support\Facades\Redis;


class GameRepository
{
    protected $start_time;
    protected $end_time;
    protected $number;
    protected $Cx_Game_Play;
    protected $Cx_Game;
    protected $Cx_User;
    protected $Cx_Game_Betting;
    protected $UserRepository;
    protected $Cx_User_Balance_Logs;
    protected $Cx_Game_Config;
    protected $Cx_Date_Prize;
    protected $Cx_System;
    protected $Cx_User_Recharge_Logs;



    public function __construct(Cx_User_Recharge_Logs $Cx_User_Recharge_Logs,Cx_System $Cx_System,Cx_Date_Prize $Cx_Date_Prize,Cx_Game_Config $Cx_Game_Config,Cx_User_Balance_Logs $Cx_User_Balance_Logs,Cx_Game_Play $Cx_Game_Play,  UserRepository $UserRepository,Cx_Game_Betting $Cx_Game_Betting,Cx_User $Cx_User,Cx_Game $Cx_Game)
    {
        $this->Cx_Game_Config = $Cx_Game_Config;
        $this->Cx_Game_Play = $Cx_Game_Play;
        $this->Cx_Game = $Cx_Game;
        $this->Cx_User = $Cx_User;
        $this->Cx_Game_Betting = $Cx_Game_Betting;
        $this->UserRepository = $UserRepository;
        $this->Cx_User_Balance_Logs = $Cx_User_Balance_Logs;
        $this->Cx_Date_Prize=$Cx_Date_Prize;
        $this->Cx_System=$Cx_System;
        $this->Cx_User_Recharge_Logs=$Cx_User_Recharge_Logs;
    }

    //每小时生成Gold期数
    public function Generate_Gold_Number()
    {
        $return = array();
        //获取所有彩票游戏
        $data = $this->Cx_Game->where("id", 1)->where("status", 0)->get();
        for ($i = 0; $i < count($data); $i++) {
            $arr = array();
            //获取当前游戏是否生成过期数
            $count = $this->Cx_Game_Play->where("game_id", $data[$i]->id)->orderBy('end_time', 'desc')->count();
            if ($count > 0) {
                $row = $this->Cx_Game_Play->where("game_id", $data[$i]->id)->orderBy('end_time', 'desc')->first();
            }
            for ($j = 0; $j < (60*24); $j++) {
                if ($count > 0) {
                    //获取最新一期信息
                    if ($j == 0) {
                        $this->start_time = $row->end_time;
                        $this->end_time = $this->start_time + 60;
                        $this->number = date('YmdH', $this->start_time)."1".$j;
                    } else {
                        $this->start_time = $this->end_time;
                        $this->end_time = $this->end_time + 60;
                        $this->number = date('YmdH', $this->start_time)."1".$j;
                    }
                } else {
                    if ($j == 0) {
                        $this->start_time = strtotime(date("Y-m-d",time()));
                        $this->end_time = $this->start_time + 60;
                        $this->number = date('YmdH', $this->start_time)."1".$j;
                    } else {
                        $this->start_time = $this->end_time;
                        $this->end_time = $this->end_time + 60;
                        $this->number = date('YmdH', $this->start_time)."1".$j;
                    }
                }

                $arr["game_id"] = $data[$i]->id;
                $arr["number"] = $this->number;
                $arr["start_time"] = $this->start_time;
                $arr["end_time"] = $this->end_time;
                $arr["status"] = 0;
                array_push($return, $arr);
            }
        }

        $this->Cx_Game_Play->insert($return);
    }
    //每小时生成Silver期数
    public function Generate_Silver_Number()
    {
        $return = array();
        //获取所有彩票游戏
        $data = $this->Cx_Game->where("id", 2)->where("status", 0)->get();
        for ($i = 0; $i < count($data); $i++) {
            $arr = array();
            //获取当前游戏是否生成过期数
            $count = $this->Cx_Game_Play->where("game_id", $data[$i]->id)->orderBy('end_time', 'desc')->count();
            if ($count > 0) {
                $row = $this->Cx_Game_Play->where("game_id", $data[$i]->id)->orderBy('end_time', 'desc')->first();
            }
            for ($j = 0; $j < (30*24); $j++) {
                if ($count > 0) {
                    //获取最新一期信息
                    if ($j == 0) {
                        $this->start_time = $row->end_time;
                        $this->end_time = $this->start_time + 120;
                        $this->number = date('YmdH', $this->start_time)."2".$j;
                    } else {
                        $this->start_time = $this->end_time ;
                        $this->end_time = $this->end_time + 120;
                        $this->number = date('YmdH', $this->start_time)."2".$j;
                    }
                } else {
                    if ($j == 0) {
                        $this->start_time = strtotime(date("Y-m-d",time()));
                        $this->end_time = $this->start_time + 120;
                        $this->number = date('YmdH', $this->start_time)."2".$j;
                    } else {
                        $this->start_time = $this->end_time ;
                        $this->end_time = $this->end_time + 120;
                        $this->number = date('YmdH', $this->start_time)."2".$j;
                    }
                }

                $arr["game_id"] = $data[$i]->id;
                $arr["number"] = $this->number;
                $arr["start_time"] = $this->start_time;
                $arr["end_time"] = $this->end_time;
                $arr["status"] = 0;
                array_push($return, $arr);
            }
        }

        $this->Cx_Game_Play->insert($return);
    }
    //每小时生成Jewelry期数
    public function Generate_Jewelry_Number()
    {
        $return = array();
        //获取所有彩票游戏

        $data = $this->Cx_Game->where("id", 3)->where("status", 0)->get();
        for ($i = 0; $i < count($data); $i++) {
            $arr = array();
            //获取当前游戏是否生成过期数
            $count = $this->Cx_Game_Play->where("game_id", $data[$i]->id)->orderBy('end_time', 'desc')->count();
            if ($count > 0) {
                $row = $this->Cx_Game_Play->where("game_id", $data[$i]->id)->orderBy('end_time', 'desc')->first();
            }
            for ($j = 0; $j < (20*24); $j++) {
                if ($count > 0) {
                    //获取最新一期信息
                    if ($j == 0) {
                        $this->start_time = $row->end_time;
                        $this->end_time = $this->start_time + 180;
                        $this->number = date('YmdH', $this->start_time)."3".$j;
                    } else {
                        $this->start_time = $this->end_time ;
                        $this->end_time = $this->end_time + 180;
                        $this->number = date('YmdH', $this->start_time)."3".$j;
                    }
                } else {
                    if ($j == 0) {
                        $this->start_time = strtotime(date("Y-m-d",time()));
                        $this->end_time = $this->start_time + 180;
                        $this->number = date('YmdH', $this->start_time)."3".$j;
                    } else {
                        $this->start_time = $this->end_time ;
                        $this->end_time = $this->end_time + 180;
                        $this->number = date('YmdH', $this->start_time)."3".$j;
                    }
                }

                $arr["game_id"] = $data[$i]->id;
                $arr["number"] = $this->number;
                $arr["start_time"] = $this->start_time;
                $arr["end_time"] = $this->end_time;
                $arr["status"] = 0;
                array_push($return, $arr);
            }
        }

        $this->Cx_Game_Play->insert($return);
    }
    //每小时生成Other期数
    public function Generate_Other_Number()
    {
        $return = array();
        //获取所有彩票游戏

        $data = $this->Cx_Game->where("id", 4)->where("status", 0)->get();
        for ($i = 0; $i < count($data); $i++) {
            $arr = array();
            //获取当前游戏是否生成过期数
            $count = $this->Cx_Game_Play->where("game_id", $data[$i]->id)->orderBy('end_time', 'desc')->count();
            if ($count > 0) {
                $row = $this->Cx_Game_Play->where("game_id", $data[$i]->id)->orderBy('end_time', 'desc')->first();
            }
            for ($j = 0; $j < (12*24); $j++) {
                if ($count > 0) {
                    //获取最新一期信息
                    if ($j == 0) {
                        $this->start_time = $row->end_time;
                        $this->end_time = $this->start_time + 300;
                        $this->number = date('YmdH', $this->start_time)."4".$j;
                    } else {
                        $this->start_time = $this->end_time ;
                        $this->end_time = $this->end_time + 300;
                        $this->number = date('YmdH', $this->start_time)."4".$j;
                    }
                } else {
                    if ($j == 0) {
                        $this->start_time = strtotime(date("Y-m-d",time()));
                        $this->end_time = $this->start_time + 300;
                        $this->number = date('YmdH', $this->start_time)."4".$j;
                    } else {
                        $this->start_time = $this->end_time;
                        $this->end_time = $this->end_time + 300;
                        $this->number = date('YmdH', $this->start_time)."4".$j;
                    }
                }

                $arr["game_id"] = $data[$i]->id;
                $arr["number"] = $this->number;
                $arr["start_time"] = $this->start_time;
                $arr["end_time"] = $this->end_time;
                $arr["status"] = 0;
                array_push($return, $arr);
            }
        }

        $this->Cx_Game_Play->insert($return);
    }
    //根据彩票游戏id获取本期信息和上期开奖号码
    public function Game_Start($id,$user_id)
    {
        sleep(1);
        $time = time();
        $bq_game = $this->Cx_Game_Play->where("game_id", $id)->where('start_time', "<", $time)->where('end_time', ">", $time)->first();
        if (!isset($bq_game->number)) {
            $bq_game = $this->Cx_Game_Play->where("game_id", $id)->where('start_time', "<", ($time + 2))->where('end_time', ">", $time)->first();
        }
        $sq_game = $this->Cx_Game_Play->where("game_id", $id)->where('number', ($bq_game->number - 1))->first();
//        $pr_lx = $this->Cx_Game_Play->select("number","prize_number","type")->where("game_id", $id)->where("number", "<",$bq_game->number)->orderBy('start_time', 'desc')->limit(10)->get();
        $pr_lx = $this->Cx_Game_Play->select("number","prize_number","type")->where("game_id", $id)->where("id", "<",$bq_game->id)->orderBy('start_time', 'desc')->limit(10)->get();
        $lx_game = $this->Cx_Game_Betting->with(array(
                'game_play' => function ($query) {
                    $query->select('id', 'number', 'prize_number');
                },
                'game_c_x' => function($query){
                    $query->select('id', 'name', 'name as name_india');
                }
            )
        )->where("user_id", $user_id)->where("game_id", $id)->where('betting_time', "<",$time)->orderBy('betting_time', 'desc')->limit(4)->get();
        $user_obj =$this->Cx_User->where('id',$user_id)->first();
        unset($sq_game->game_id, $sq_game->prize_time);

        $row['sq'] = $sq_game;
        $row['bq'] = $bq_game;
        $row['lx'] = $lx_game;  //历史订单
        $row['pr'] = $pr_lx;
        $row['balance'] = $user_obj->balance;
        $row['count_down'] = ($bq_game->end_time - time());
        return $row;
    }
    //投注接口
    public function Betting($user, $data)
    {
        $row = explode("|", $data['game_c_x_id']);

        $money = $data['money'] / count($row);
        foreach ($row as $key => $val) {
            $arr = array();
            $arr['betting_num'] =  $data['game_id'] . $val . $user->id . $key.time();
            $arr['user_id'] = $user->id;
            $arr['game_id'] = $data['game_id'];
            $arr['game_p_id'] = $data['game_p_id'];
            $arr['game_c_x_id'] = $val;
            $arr['money'] = $money-($money * 0.03);
            $arr['betting_time'] = time();
            $arr['status'] = 0;
            $arr['type'] = 0;
            $arr["service_charge"] = $money * 0.03;

            //开启事务
//            DB::beginTransaction();
//            try {
                //写入投注表
                $user_obj = $this->Cx_User->where('id', $user->id)->first();
                $this->Cx_Game_Betting->insert($arr);
                //减少用户余额
                $u_money = $user_obj->balance - $money;
                $cl_betting = $user_obj->cl_betting + $money;
                $cl_betting_total = $user_obj->cl_betting_total + 1;
                $this->Cx_User->where('id', $user->id)->update(['balance' => $u_money,'cl_betting' => $cl_betting,'cl_betting_total' => $cl_betting_total]);
                $this->Cx_User_Balance_Logs->insert(array("user_id" => $user->id, "type" => 1, "dq_balance" => $user_obj->balance, "wc_balance" => $u_money, "time" => time(), "msg" => "下注减少金额" . $money, "money" => $money));
//                if ($user_obj->agent_id) {
//                    // 计算代理的收益
//                    $this->common->getAgentIds($user_obj->agent_id);
//                    $this->BubbleSorting($this->common->agentIds, $money);
//                }

                //增加资金记录
//                $connection = Mongodb::connectionMongodb('cx_user_balance_logs');
//                $connection->insert(array("user_id" => $user->id, "type" => 1, "dq_balance" => $user_obj->balance, "wc_balance" => $u_money, "time" => $arr['betting_time'], "msg" => "投注扣除金额" . $money));
//                DB::commit();
//            } catch (\Exception $e) {
//                DB::rollBack();
//                return false;
//            }

            $this->UserRepository->updateCacheUser($user->id, $user_obj);
        }
        $user_new = $this->Cx_User->where('id', $user->id)->first();
        return $user_new->balance;

    }
    //根据id获取期数信息
    public function Get_Game_Play_ById($game_p_id)
    {
        return $this->Cx_Game_Play->where("id", $game_p_id)->first();
    }
    //获取可结算自动开奖的期数
    public function Get_Settlement()
    {
        $data = $this->Cx_Game_Play->where('status', 0)->where('is_status', 0)->where('end_time', "<=", time())->where('is_queue', 0)->get();
        if (count($data) > 0) {
            $arr = array();
            foreach ($data as $val) {
                array_push($arr, $val->id);
            }
            $this->Cx_Game_Play->whereIn('id', $arr)->update(['is_queue' => 1]);

            return $data;
        } else {
            return $data;
        }
    }
    //获取可结算手动开奖的期数
    public function Get_Settlement_Sd()
    {
        $data = $this->Cx_Game_Play->where('status', 0)->where('is_status', 1)->where('end_time', "<=", time())->where('is_queue', 0)->get();
        if (count($data) > 0) {
            $arr = array();
            foreach ($data as $val) {
                array_push($arr, $val->id);
            }
            $this->Cx_Game_Play->whereIn('id', $arr)->update(['is_queue' => 1]);

            return $data;
        } else {
            return $data;
        }
    }
    //根据期数id判断是否已添加进队列
    public function Game_Is_Queue($play_id){
        $count=$this->Cx_Game_Play->where('id', $play_id)->count();
        if ($count>0){
            $data = $this->Cx_Game_Play->where('id', $play_id)->first();
            if($data->is_queue==0){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }

    }
    //执行手动开奖设置
    public function Carried_Sd_Prize($number,$play_id){

        if($this->Cx_Game_Play->where('id', $play_id)->update(['is_status' => 1,'prize_number' => $number])){
            return true;
        }else{
            return false;
        }
    }
    //根据下注项id分组查询下注项总金额
    public function GroupBy_W($play_id)
    {
        return $this->Cx_Game_Betting->with(array(
                'game_c_x' => function ($query) {
                    $query->select('id', 'odds');
                },
            )
        )->where("game_p_id", $play_id)->select('game_c_x_id', $this->Cx_Game_Betting->raw('SUM(money) as money'))->groupBy('game_c_x_id')->get();
    }
    //根据期数ID，玩法ID获取用户下注信息
    public function Get_Betting($play_id)
    {
        $data = $this->Cx_Game_Betting->where("game_p_id", $play_id)->get();
        if (count($data) > 0) {
            return $data;
        } else {
            return false;
        }
    }
    //结算用户投注
    public function Result_Entry($betting, $type,$odds)
    {
        $time = time();
        $arr = array();
        $date=date('Y-m-d',time());
        $date_count=$this->Cx_Date_Prize->where("date",$date)->count();
        if($date_count>0){
            $date_data=$this->Cx_Date_Prize->where("date",$date)->first();
        }else{
            $this->Cx_Date_Prize->insert(array("date" => $date));
            $date_data=$this->Cx_Date_Prize->where("date",$date)->first();
        }

        if ($type == 1) {//赢
            $arr['settlement_time'] = $time;
            $arr['status'] = 1;
            $arr['type'] = 1;
            $arr['win_money'] = ($odds * $betting->money);
            $arr['odds'] = $odds;
            $this->Cx_Game_Betting->where("id", $betting->id)->update($arr);
            $user_obj = $this->Cx_User->where('id', $betting->user_id)->first();
            $zx_money = $user_obj->balance + $arr['win_money'];
            $this->Cx_User->where('id', $betting->user_id)->update(['balance' => $zx_money]);
            //增加资金记录
            $this->Cx_User_Balance_Logs->insert(array("user_id" => $betting->user_id, "type" => 6, "dq_balance" => $user_obj->balance, "wc_balance" => $zx_money, "time" => $time, "msg" => "中奖增加金额" . $arr['win_money'], "money" => $arr['win_money']));
            if($user_obj->reg_source_id==0){
                $date_arr =array();
                $date_arr['pt_s_money']=$date_data->pt_s_money+ $arr['win_money'];
                $date_arr['b_money']=$date_data->b_money+$betting->money;
                //$date_arr['pt_money']=$date_arr['b_money']-$date_arr['pt_s_money'];
                $this->Cx_Date_Prize->where("id", $date_data->id)->update($date_arr);
            }else if($user_obj->reg_source_id==1){
                $date_arr =array();
                $date_arr['c_pt_s_money']=$date_data->c_pt_s_money+ $arr['win_money'];
                $date_arr['c_b_money']=$date_data->c_b_money+$betting->money;
                //$date_arr['c_pt_money']=$date_arr['c_b_money']-$date_arr['c_pt_s_money'];
                $this->Cx_Date_Prize->where("id", $date_data->id)->update($date_arr);
            }

        } else if ($type == 2) {//输
            $arr['settlement_time'] = $time;
            $arr['status'] = 2;
            $arr['type'] = 1;
            $arr['odds'] = $odds;
            $arr['win_money'] = 0;
            $this->Cx_Game_Betting->where("id", $betting->id)->update($arr);
            $user_obj = $this->Cx_User->where('id', $betting->user_id)->first();
            if($user_obj->reg_source_id==0){
                $date_arr =array();
                $date_arr['b_money']=$date_data->b_money+$betting->money;
                $date_arr['pt_money']=$date_data->pt_money+$betting->money;
                $this->Cx_Date_Prize->where("id", $date_data->id)->update($date_arr);
            }else if($user_obj->reg_source_id==1){
                $date_arr =array();
                $date_arr['c_b_money']=$date_data->c_b_money+$betting->money;
                $date_arr['c_pt_money']=$date_data->c_pt_money+$betting->money;
                $this->Cx_Date_Prize->where("id", $date_data->id)->update($date_arr);
            }
        }
        return true;
    }
    //结算场次信息
    public function Play_Result_Entry($play_id, $result, $isWin, $winmoney, $lostmoney, $winmoney1, $result1)
    {
        //echo $play_id."--".$result."--".$isWin."--".$winmoney."--".$lostmoney."--".$winmoney1."--".$result1;
//        $date=date('Y-m-d',time());
//        $date_count=$this->Cx_Date_Prize->where("date",$date)->count();
//        if($date_count>0){
//            $date_data=$this->Cx_Date_Prize->where("date",$date)->first();
//        }else{
//            $this->Cx_Date_Prize->insert(array("date" => $date));
//            $date_data=$this->Cx_Date_Prize->where("date",$date)->first();
//        }
        $arr['prize_number'] = $result;
        $arr['status'] = 1;
        $arr['prize_time'] = time();
        $date_arr=array();
        $b_money = $winmoney1;
        if ($isWin == 1) {
            $arr['type'] = 1;
            $arr['pt_money'] = $b_money - $winmoney;
//            $date_arr['pt_money']=$date_data->pt_money+$arr['pt_money'];
        } elseif ($isWin == 2) {
            $arr['type'] = 2;
            $arr['pt_money'] = $b_money - $winmoney;
//            $date_arr['pt_s_money']=$date_data->pt_s_money+abs($arr['pt_money']);
        } elseif ($isWin == 3) {
            $arr['type'] = 3;
            $arr['pt_money'] = $b_money - $lostmoney;
        }
//        $date_arr['b_money']=$date_data->b_money+$b_money;
        $arr['winmoney'] = $winmoney;
        $arr['b_money'] = $b_money;
        $arr['lostmoney'] = $lostmoney;
//        $this->Cx_Date_Prize->where("id", $date_data->id)->update($date_arr);
        $this->Cx_Game_Play->where("id", $play_id)->update($arr);
        return true;
    }
    //根据ID获取期数统计并判断为未开奖
    public function Get_Info($id)
    {
        return $this->Cx_Game_Play->where("id", $id)->where("status", 0)->count();
    }
    //根据ID获取当期下注总金额
    public function Get_Betting_Sum($play_id)
    {
        return $this->Cx_Game_Betting->where("game_p_id", $play_id)->sum('money');
    }
    //根据下注项id分组查询新用户个数
    public function Get_Betting_Usering($play_id){
        $a=$this->Cx_Game_Betting->with(array(
                'users' => function ($query) {
                    $query->where('new_old', 1);
                }
            )
        )->select("game_c_x_id as x_id",$this->Cx_User->raw('COUNT(id) as count'))->where("game_p_id", $play_id)->groupBy('game_c_x_id')->get()->toArray();
        foreach ($a as $key => $value){
            $name=$this->Cx_Game_Config->where("id",$value['x_id'])->select("name")->first()->toArray();
            $a[$key]['name']=$name['name'];
        }
        return $a;
    }
    //获取总杀率设置
    public function Get_System(){
        //$data=$this->Cx_System->first();
        if(Redis::exists("SYSTEM_CONFIG")){
            $data=json_decode(Redis::get("SYSTEM_CONFIG"));
        }else{
            $data=$this->Cx_System->first();
            Redis::set("SYSTEM_CONFIG", json_encode($data,JSON_UNESCAPED_UNICODE));
        }
        return $data;
    }
    public function Get_Date_Money(){
        $date=date('Y-m-d',time());
        $date_count=$this->Cx_Date_Prize->where("date",$date)->count();
        if($date_count>0){
            $date_data=$this->Cx_Date_Prize->where("date",$date)->first();
        }else{
            $this->Cx_Date_Prize->insert(array("date" => $date));
            $date_data=$this->Cx_Date_Prize->where("date",$date)->first();
        }
        return $date_data;
    }
    public function Get_New_Sum_Money(){
        $s = strtotime(date('Y-m-d').'00:00:00');
        $l = strtotime(date('Y-m-d').'23:59:59');
        $ids=array_column($this->Cx_User->where("reg_source_id", 0)->get("id")->toArray(), "id");
        $data['y_money']=$this->Cx_Game_Betting->whereBetween('betting_time', [$s, $l])->whereIn("user_id",$ids)->where("status",1)->sum("win_money");
        $s1_money=$this->Cx_Game_Betting->whereBetween('betting_time', [$s, $l]) ->whereIn("user_id",$ids)->where("status",1)->sum("money");
        $s2_money=$this->Cx_Game_Betting->whereBetween('betting_time', [$s, $l])->whereIn("user_id",$ids)->where("status",2)->sum("money");
        $data['s_money']=$s1_money+$s2_money;
        $data['c_money']=$this->Cx_User_Recharge_Logs->whereBetween('time', [$s, $l])->where("status",2)->sum("money");
        return $data;
    }

    public function Get_New_Sum_Money1(){
        $s = strtotime(date('Y-m-d').'00:00:00');
        $l = strtotime(date('Y-m-d').'23:59:59');
        $ids=array_column($this->Cx_User->where("reg_source_id", 0)->get("id")->toArray(), "id");
        $data['y_money']=$this->Cx_Game_Betting->whereBetween('betting_time', [$s, $l])->whereIn("user_id",$ids)->where("status",1)->sum("win_money");
        $s1_money=$this->Cx_Game_Betting->whereBetween('betting_time', [$s, $l]) ->whereIn("user_id",$ids)->where("status",1)->sum("money");
        $s2_money=$this->Cx_Game_Betting->whereBetween('betting_time', [$s, $l])->whereIn("user_id",$ids)->where("status",2)->sum("money");
        $data['s_money']=$s1_money+$s2_money;
        $data['c_money']=$this->Cx_User_Recharge_Logs->whereBetween('time', [$s, $l])->where("status",2)->sum("money");
        $date_sj_kill=($data['s_money']-$data['y_money'])/$data['c_money'];
        if($date_sj_kill<=0){
            $data['isWin']=1;
        }else{
            $p_kill=$date_sj_kill-0.51;
            if($p_kill>0.05){
                $data['isWin']=2;
            }else{
                $data['isWin']=1;
            }
        }
        $data['msg']="今日用户输总金额".$data['s_money']."今日用户赢总金额".$data['y_money']."今日用户充值总金额".$data['c_money']."实际杀率".$date_sj_kill;
        return $data;
    }
    //投注列表.
    public function Betting_List($user, $limit, $offset)
    {
        return $this->Cx_Game_Betting->with(array(
                'game_c_x' => function ($query) {
                    $query->select('id', 'name', 'name as name_india');
                },
                'game_name' => function ($query) {
                    $query->select('id', 'name');
                },
                'game_play' => function ($query) {
                    $query->select('id', 'number', 'prize_number');
                }
            )
        )->where("user_id", $user)->offset($offset)->limit($limit)->orderBy('betting_time', 'desc')->get();

    }
    //开奖列表
    public function Game_List($game_id,$limit, $offset){
        return $this->Cx_Game_Play->with(array(
                'game_name_p' => function ($query) {
                    $query->select('id', 'name');
                }
            )
        )->where("game_id", $game_id)->where("status",1)->offset($offset)->limit($limit)->orderBy('start_time', 'desc')->get();
    }

}
