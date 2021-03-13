<?php

namespace App\Http\Controllers;

use App\Libs\Aes;
use App\Libs\Uploads\Uploads;
use App\Models\Cx_Game_Betting;
use App\Models\Cx_Game_Play;
use App\Models\Cx_User;
use App\Repositories\Game\GameRepository;
use App\Services\Game\Ssc_FourService;
use App\Services\Game\Ssc_TwoService;
use App\Services\Game\SscService;
use App\Services\Pay\Winpay;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Request;
use Predis\Client;

class TestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $Cx_Game_Play, $Cx_Game_Betting, $GameRepository, $Ssc_FourService, $Cx_User;

    public function __construct(Cx_Game_Betting $game_Betting, Cx_Game_Play $game_Play, GameRepository $gameRepository, Ssc_FourService $ssc_FourService, Cx_User $cx_User){
        $this->Cx_Game_Play = $game_Play;
        $this->Cx_Game_Betting = $game_Betting;
        $this->GameRepository = $gameRepository;
        $this->Ssc_FourService = $ssc_FourService;
        $this->Cx_User = $cx_User;
    }

    public function upload()
    {
        $uploadEngine = new Uploads(null,'abcd',100000,['jpeg', 'png']);
        $path = $uploadEngine->upload('goods');
        print_r($path);
        if(!$path)echo $uploadEngine->getError();
    }

    public function testRedis()
    {
//        $redisConfig = config('database.redis.default');
//        $redis = new Client($redisConfig);
//        $data = $redis->sismember('swoft:ONLINE_USER_ID',245);
//        print_r($data);
//        $num = $redis->scard('swoft:ONLINE_USER_ID');
//        $data = $redis->get('laravel_database_GAME_CONFIG_4');
//        $aes = new Aes();
////        $data = $aes->encrypt();
//        $data = $aes->decrypt('1ayO2dTm+VwpasbQbNMm7Q==');
//        var_dump($data);
        $obj = new \ReflectionClass(Aes::class);
        $methods = $obj->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $item){
            echo $item->name . PHP_EOL;
        }
//        print_r($methods);
    }

    public function getGameResult(){
        $game_play_info = $this->Cx_Game_Play->where("id", "=", 31797)->first();
        $s = 1608048000;
        $l = 1608110100;
        $ids=array_column($this->Cx_User->where("reg_source_id", 0)->get("id")->toArray(), "id");
        $data['y_money']=$this->Cx_Game_Betting->whereBetween('betting_time', [$s, $l])->whereIn("user_id",$ids)->where("status",1)->sum("win_money");
        $s1_money=$this->Cx_Game_Betting->whereBetween('betting_time', [$s, $l]) ->whereIn("user_id",$ids)->where("status",1)->sum("money");
        $s2_money=$this->Cx_Game_Betting->whereBetween('betting_time', [$s, $l])->whereIn("user_id",$ids)->where("status",2)->sum("money");
        $data['s_money']=$s1_money+$s2_money;
        $cur_betting_money = $this->Cx_Game_Betting->where('game_p_id',31797)->sum('money');

        $can_donate_money = (1-0.3) * ($data['s_money'] + $cur_betting_money - $data['y_money']);
        $res = $this->Ssc_FourService->calculateNumMoney($game_play_info, $can_donate_money, $cur_betting_money);
        print_r($res);die;
    }

    public function test2(Winpay $winpay)
    {
        echo 123;die;
        $pay_type = '222';
        $money = 500;
        return ($winpay->rechargeOrder($pay_type, $money));
    }

    public function test(){
        try{
            $phone = request()->input('phone');
            $res = Redis::set("REGIST_CODE:" . $phone, 666666);
            dd($res);
        }catch(\Exception $e){
            echo $e->getMessage();
        }

    }

    public function makeSign(){
        $params = request()->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        ksort($params);
//        print_r($params);die;
        $string = [];
        foreach ($params as $key => $value) {
            if($value != '')
                $string[] = $key . '=' . $value;
        }

//        $sign = (implode('&', $string)) . '&key=' .  'BDF2D59DB37596D5254B555437E73C37';
//        $sign = (implode('&', $string)) . '&key=' .  '8441A2291411B9D82AC889CFE3148147';
//        echo $sign;die;
        echo md5($sign);
    }

    public function openGame(SscService $sscService, Ssc_TwoService $ssc_TwoService){
        $sscService->ssc_ki(56443);
    }

    public function openGameBetting(){
        $betting_id = request()->input('betting_id');
        $betting = $this->Cx_Game_Betting->where('id', $betting_id)->first();
        if(!$betting)
            return "betting不存在";
        if($betting->status != 0)
            return  "betting处理过了";
        $game = $this->Cx_Game_Play->where('id', $betting->game_p_id)->first();
        if($game->status == 0)
            return "未开奖";
        echo $game->game_id;die;
        switch ($game->game_id){
            case 1:
                $this->result_1($game->prize_number, $betting);
                break;
            case 2:
                $this->result_2($game->prize_number, $betting);
                break;
            case 3:
                $this->result_3($game->prize_number, $betting);
                break;
            case 4:
                $this->result_4($game->prize_number, $betting);
                break;
        }
        return 'success';
    }

    public function result_1($result, $val){
        if($val->game_c_x_id==49){
            if($result==0){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==1){
            if($result==1){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==2){
            if($result==2){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==3){
            if($result==3){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==4){
            if($result==4){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==5){
            if($result==5){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==6){
            if($result==6){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==7){
            if($result==7){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==8){
            if($result==8){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==9){
            if($result==9){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==10){
            if($result==1 || $result==3 || $result==5 || $result==7 || $result==9){
                if($result==5){
                    $this->GameRepository->Result_Entry($val,1,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,1,2);
                }

            }else{
                if($result==5){
                    $this->GameRepository->Result_Entry($val,2,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,2,2);
                }

            }
        }else if($val->game_c_x_id==11){
            if($result==0 || $result==2 || $result==4 || $result==6 || $result==8){
                if($result==0){
                    $this->GameRepository->Result_Entry($val,1,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,1,2);
                }
            }else{
                if($result==0){
                    $this->GameRepository->Result_Entry($val,2,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,2,2);
                }
            }
        }else if($val->game_c_x_id==12){
            if($result==0 || $result==5 ){
                $this->GameRepository->Result_Entry($val,1,4.5);
            }else{
                $this->GameRepository->Result_Entry($val,2,4.5);
            }
        }
    }

    public function result_2($result, $val){
        if($val->game_c_x_id==50){
            if($result==0){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==13){
            if($result==1){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==14){
            if($result==2){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==15){
            if($result==3){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==16){
            if($result==4){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==17){
            if($result==5){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==18){
            if($result==6){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==19){
            if($result==7){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==20){
            if($result==8){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==21){
            if($result==9){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==22){
            if($result==1 || $result==3 || $result==5 || $result==7 || $result==9){
                if($result==5){
                    $this->GameRepository->Result_Entry($val,1,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,1,2);
                }

            }else{
                if($result==5){
                    $this->GameRepository->Result_Entry($val,2,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,2,2);
                }

            }
        }else if($val->game_c_x_id==23){
            if($result==0 || $result==2 || $result==4 || $result==6 || $result==8){
                if($result==0){
                    $this->GameRepository->Result_Entry($val,1,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,1,2);
                }
            }else{
                if($result==0){
                    $this->GameRepository->Result_Entry($val,2,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,2,2);
                }
            }
        }else if($val->game_c_x_id==24){
            if($result==0 || $result==5 ){
                $this->GameRepository->Result_Entry($val,1,4.5);
            }else{
                $this->GameRepository->Result_Entry($val,2,4.5);
            }
        }
    }

    public function result_3($result, $val){
        if($val->game_c_x_id==51){
            if($result==0){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==25){
            if($result==1){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==26){
            if($result==2){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==27){
            if($result==3){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==28){
            if($result==4){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==29){
            if($result==5){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==30){
            if($result==6){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==31){
            if($result==7){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==32){
            if($result==8){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==33){
            if($result==9){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==34){
            if($result==1 || $result==3 || $result==5 || $result==7 || $result==9){
                if($result==5){
                    $this->GameRepository->Result_Entry($val,1,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,1,2);
                }

            }else{
                if($result==5){
                    $this->GameRepository->Result_Entry($val,2,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,2,2);
                }

            }
        }else if($val->game_c_x_id==35){
            if($result==0 || $result==2 || $result==4 || $result==6 || $result==8){
                if($result==0){
                    $this->GameRepository->Result_Entry($val,1,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,1,2);
                }
            }else{
                if($result==0){
                    $this->GameRepository->Result_Entry($val,2,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,2,2);
                }
            }
        }else if($val->game_c_x_id==36){
            if($result==0 || $result==5 ){
                $this->GameRepository->Result_Entry($val,1,4.5);
            }else{
                $this->GameRepository->Result_Entry($val,2,4.5);
            }
        }
    }

    public function result_4($result, $val){
        echo $result;
        print_r($val);die;
        if($val->game_c_x_id==52){
            if($result==0){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==37){
            if($result==1){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==38){
            if($result==2){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==39){
            if($result==3){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==40){
            if($result==4){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==41){
            if($result==5){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==42){
            if($result==6){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==43){
            if($result==7){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==44){
            if($result==8){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==45){
            if($result==9){
                $this->GameRepository->Result_Entry($val,1,9);
            }else{
                $this->GameRepository->Result_Entry($val,2,9);
            }
        }else if($val->game_c_x_id==46){
            if($result==1 || $result==3 || $result==5 || $result==7 || $result==9){
                if($result==5){
                    $this->GameRepository->Result_Entry($val,1,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,1,2);
                }

            }else{
                if($result==5){
                    $this->GameRepository->Result_Entry($val,2,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,2,2);
                }

            }
        }else if($val->game_c_x_id==47){
            if($result==0 || $result==2 || $result==4 || $result==6 || $result==8){
                if($result==0){
                    $this->GameRepository->Result_Entry($val,1,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,1,2);
                }
            }else{
                if($result==0){
                    $this->GameRepository->Result_Entry($val,2,1.5);
                }else{
                    $this->GameRepository->Result_Entry($val,2,2);
                }
            }
        }else if($val->game_c_x_id==48){
            if($result==0 || $result==5 ){
                $this->GameRepository->Result_Entry($val,1,4.5);
            }else{
                $this->GameRepository->Result_Entry($val,2,4.5);
            }
        }
    }

    public function initInviteRelation(){
        $ids = DB::table('users')->where("is_customer_service",1)->pluck('id')->toArray();
        foreach($ids as $id){
            $this->handleRelation($id,"");
        }
        echo 'success--1';
        $user_ids = DB::table('users')->where("is_customer_service",0)->whereNull('one_recommend_id')->whereNull('two_recommend_id')->pluck('id')->toArray();
        foreach($user_ids as $user_id){
            $this->handleRelation($user_id,"");
        }
        echo 'success--2';
    }

    public function handleRelation($id, $relation){
        print_r("-" .$id. "-");
        $table = DB::table('users');
        $ids = $table->where('two_recommend_id', $id)->pluck('id')->toArray();
        if($ids){
            $invite_relation = makeInviteRelation($relation, $id);
            $table->whereIntegerInRaw('id',$ids)->update(['invite_relation'=>$invite_relation]);
            foreach($ids as $index){
                $this->handleRelation($index,$invite_relation);
            }
        }else{
            return true;
        }
    }

    public function initInviteRelation2(){
        $level = request()->input('level');
        $handle_ids = $select_ids = [];
        $table = DB::table('users');
        switch ($level){
            case 1:
                $list = $table->whereNotNull("customer_service_id")->whereNull('invite_relation')->select(['id', 'customer_service_id'])->get();
                if(!$list->isEmpty()){
                    foreach($list as $key => $item){
                        $select_ids[] = $item->id;
                        $table->where("id", $item->id)->update(['invite_relation'=>makeInviteRelation("", $item->customer_service_id)]);
                        $handle_ids[] = $item->id;
                    }
                }
                break;
            case 2:
                $list2 = $table->whereNull('customer_service_id')->whereNull('one_recommend_id')->whereNull('invite_relation')->whereNotNull('two_recommend_id')->select(['id', 'two_recommend_id'])->get();
                if(!$list2->isEmpty()){
                    foreach($list2 as $k => $i){
                        $select_ids[] = $i->id;
                        $relation = $table->where("id", $i->two_recommend_id)->select(['invite_relation', 'id'])->first();
                        if(!$relation){
                            continue;
                        }else{
                            $table->where("id", $i->id)->update(['invite_relation'=>makeInviteRelation($relation->invite_relation, $i->two_recommend_id)]);
                            $handle_ids[] = $i->id;
                        }
                    }
                }
                break;
            case 3:
                $list3 = $table->whereNull('customer_service_id')->whereNull('invite_relation')->whereNotNull('one_recommend_id')->whereNotNull('two_recommend_id')->select(['id', 'two_recommend_id'])->get();
                if(!$list3->isEmpty()){
                    foreach($list3 as $k3 => $i3){
                        $select_ids[] = $i3->id;
                        $relation = $table->where("id", $i3->two_recommend_id)->select(['invite_relation', 'id'])->first();
                        if(!$relation){
                            continue;
                        }else{
                            $table->where("id", $i3->id)->update(['invite_relation'=>makeInviteRelation($relation->invite_relation, $i3->two_recommend_id)]);
                            $handle_ids[] = $i3->id;
                        }
                    }
                }
                break;
            default:
                echo $level;
                break;
        }
        print_r($handle_ids);
        print_r($select_ids);

    }

    public function aesDecrypt()
    {
        $string = request()->input('str');
        print_r(json_decode(aesDecrypt($string),true));
    }

    public function test3(){
        sleep(10);
        echo 1;
    }

}
