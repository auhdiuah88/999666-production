<?php


namespace App\Repositories\Game;



use App\Models\Cx_Game_Betting;
use App\Models\Cx_User;
use App\Mongodb;
use App\Repositories\Api\UserRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\Cx_Game_Config;
use App\Models\Cx_Game_Play;



class SscRepository
{
    protected $Cx_Game_Config;
    protected $Cx_Game_Play;
    protected $Ssc_Key="SSC:CONFIG";
    protected $Cx_Game_Betting;
    protected $Cx_User;
    protected $UserRepository;

    public function __construct(Cx_Game_Config $Cx_Game_Config,Cx_Game_Play $Cx_Game_Play,Cx_Game_Betting $Cx_Game_Betting,Cx_User $Cx_User,UserRepository $UserRepository)
    {
        $this->Cx_Game_Config=$Cx_Game_Config;
        $this->Cx_Game_Play=$Cx_Game_Play;
        $this->Cx_Game_Betting=$Cx_Game_Betting;
        $this->Cx_User=$Cx_User;
        $this->UserRepository=$UserRepository;
    }
    public function Get_Config($id){
        //验证缓存键是否存在
        if(Cache::has($this->Ssc_Key.":".$id)){
            return Cache::get($this->Ssc_Key.":".$id);
        }else{
            $data=$this->Cx_Game_Config->where("game_id",$id)->where("type",0)->get();
            $return=array();
            foreach ($data as $val){
                $arr=array();
                $arr['name']=$val->name;
                $arr['id']=$val->id;
                $arr['data']=$this->Cx_Game_Config->where("game_id",$id)->where("type",1)->where("game_c_id",$val->id)->get()->toArray();
                array_push($return,$arr);
            }
            Cache::put($this->Ssc_Key.":".$id , $return);
            return $return;
        }

    }
    public function gewei($play_id,$game_c_w_id){
        $count=$this->Cx_Game_Betting->where("game_p_id",$play_id)->where("game_c_w_id",$game_c_w_id)->count();
        if($count>0){
           return $this->Cx_Game_Betting->where("game_p_id",$play_id)->where("game_c_w_id",$game_c_w_id)->get();
        }else{
            return false;
        }

    }
    //玩法分组统计总下注金额
    public function gewei_count($play_id,$game_c_w_id){
        return $this->Cx_Game_Betting->where("game_p_id",$play_id)->where("game_c_w_id",$game_c_w_id)->select('game_c_x_id',  $this->Cx_Game_Betting->raw('SUM(money) as money'))->groupBy('game_c_x_id')->get();

    }
    public function Get_Betting($play_id,$game_c_w_id){
        $data=$this->Cx_Game_Betting->where("game_p_id",$play_id)->where("game_c_w_id",$game_c_w_id)->get();
        if (count($data)>0){
            return $data;
        }else{
            return false;
        }
    }
    //根据ID获取期数信息
    public function Get_Info($id){
        return $this->Cx_Game_Play->where("id",$id)->where("status",0)->count();
    }
    //结算用户投注
    public function Result_Entry($betting,$type){
        $time=time();
        if($type==1){//赢
            $odds=$this->Cx_Game_Config->where('id',$betting->game_c_x_id)->select("odds")->first();
            $arr['settlement_time']=$time;
            $arr['status']=1;
            $arr['type']=1;
            $arr['win_money']=($odds->odds*$betting->money);
            $this->Cx_Game_Betting->where("id",$betting->id)->update($arr);
            $user_obj=$this->Cx_User->where('id', $betting->user_id)->first();
            $zx_money=$user_obj->balance+$arr['win_money'];
            $this->Cx_User->where('id', $betting->user_id)->update(['balance' =>$zx_money]);
            //增加资金记录
            $connection = Mongodb::connectionMongodb('cx_user_balance_logs');
            $connection->insert(array("user_id"=>$betting->user_id,"type"=>2,"dq_balance"=>$user_obj->balance,"wc_balance"=>$zx_money,"time"=> $time));
            $this->UserRepository->updateCacheUser($betting->user_id,$user_obj);
        }else if($type==2){//输
            $arr['settlement_time']=$time;
            $arr['status']=2;
            $arr['type']=1;
            $this->Cx_Game_Betting->where("id",$betting->id)->update($arr);
        }
        return true;
    }
    //结算场次信息
    public function Play_Result_Entry($play_id,$result,$isWin,$winmoney,$lostmoney){
        $arr['prize_number']=$result;
        $arr['status']=1;
        $arr['prize_time']=time();
        if($isWin==1){
            $arr['type']=1;
            $arr['pt_money']=$lostmoney-$winmoney;
        }elseif($isWin==2){
            $arr['type']=2;
            $arr['pt_money']=$winmoney-$lostmoney;
        }elseif($isWin==3){
            $arr['type']=3;
            $arr['pt_money']=$winmoney-$lostmoney;
        }
        $arr['winmoney']=$winmoney;
        $arr['lostmoney']=$lostmoney;
        $this->Cx_Game_Play->where("id",$play_id)->update($arr);
        return true;
    }


}
