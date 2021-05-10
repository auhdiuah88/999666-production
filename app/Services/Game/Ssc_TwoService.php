<?php


namespace App\Services\Game;

use App\Repositories\Game\GameRepository;
use App\Repositories\Game\SscRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Ssc_TwoService
{
    protected $SscRepository;
    protected $winmoney;
    protected $lostmoney;
    protected $winmoney1;
    protected $GameRepository;
    protected $REDIS_LOGIN="REDIS_ZONG_SHALV";
    protected $game_id = 2;

    public function __construct(SscRepository $SscRepository,GameRepository $GameRepository)
    {
        $this->SscRepository=$SscRepository;
        $this->GameRepository=$GameRepository;
    }
    public function Get_Config($id){
        return $this->SscRepository->Get_Config($id);
    }

    //生成随机的开奖结果
    public function getResult()
    {
        $seed = array(0,1,2,3,4,5,6,7,8,9);
        $str = '';
        $str1 = '';
        for($i=0;$i<1;$i++) {
            $rand = rand(0,count($seed)-1);
            $temp = $seed[$rand];
            $str .= $temp;
            $str1 .= $temp;
            unset($seed[$rand]);
            $seed = array_values($seed);
        }
        $arr['str']=$str;
        $arr['str1']=$str1;
        return $arr;
    }

    public function ssc_ki($play_id){
        try{
            $Is_Executive_Prize=$this->GameRepository->Get_Info($play_id);
            if($Is_Executive_Prize<=0){
                return false;
            }
            ##获取本期的情况
            $game_play_info = $this->GameRepository->Get_Game_play($play_id);
            ##本局的下注金额
            $cur_betting_money = $this->GameRepository->Get_Cur_Betting_Money($play_id);
            //单局杀率判定
//        $system=$this->GameRepository->Get_System();
            $system=$this->GameRepository->Get_Game_Config($this->game_id);
//        if(!isset($system->open_type)){
//            Log::channel('game_debug')->info("开奖失败debug-22",[$system]);
//        }
//        if(!isset($system->open_type->value)){
//            Log::channel('game_debug')->info("开奖失败debug-2222",[$system]);
//        }
            $open_type = intval($system->open_type->value);
            switch ($open_type){
                case 1: //天杀
                    $date_kill = $system->date_kill;  //获得天杀率
                    ##今天内的投注金额 s_money，中奖金额 y_money
                    $new_money_sum=$this->GameRepository->Get_New_Sum_Money();
                    ##可赔金额
                    $can_donate_money = (1-$date_kill) * ($new_money_sum['s_money'] + $cur_betting_money - $new_money_sum['y_money']);
                    break;
                case 2:  //局杀
                    $kill_rate = $system->one_kill;//获得局杀率
                    $can_donate_money = (1-$kill_rate) * $cur_betting_money;
                    break;
                case 3:  //随机
                    $can_donate_money = 0;
                    break;
                default:
                    $can_donate_money = 0;
                    Log::channel('kidebug')->error('开奖处出现异常',compact('play_id'));
                    break;
            }
            $calc = $this->calculateNumMoney($game_play_info, $can_donate_money, $cur_betting_money, $open_type);

            ##输赢 1赢 2输 3平
            $type = $calc['win_money'] > $cur_betting_money ? 2 : ($calc['win_money'] < $cur_betting_money ? 1 : 3) ;
            ##用户输的钱
            $lostmoney = $cur_betting_money - $calc['win_money'];
            ##平台赢的钱
            $pt_money = $lostmoney;
            ##结算
            $this->Ki_Executive_Prize($calc['result'],$play_id, $calc['win_money'], $lostmoney, $type, $pt_money, $cur_betting_money);

            return true;
        }catch(\Exception $e){

            return false;
        }

    }

    public function calculateResult($temp_win_money, $win_money, $can_donate_money, $result_array, $n){
//        echo "++" . $n .'<===>'.$win_money . "++";
        if($temp_win_money == $win_money){
            echo "=";
            $result_array[] = $n;
        }
        if($temp_win_money > $win_money && $temp_win_money > $can_donate_money){
            echo ">";
            $temp_win_money = $win_money;
            $result_array = [$n];
        }
        if($temp_win_money < $win_money && $win_money <= $can_donate_money){
            echo "<";
            $temp_win_money = $win_money;
            $result_array = [$n];
        }
        return compact('result_array','temp_win_money');
    }

    public function calculateNumMoney($game_play_info, $can_donate_money, $cur_betting_money, $open_type){
        ##没有人投注[随机出结果]
        if($open_type == 3 || $cur_betting_money <= 0){
            $result_array = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
            $idx = mt_rand(0, 9);
            $result = $result_array[$idx];
            $win_money = 0;
            return compact('result','win_money');
        }
        ##奇数
        $win_money_odd = $this->GameRepository->calc_odd($game_play_info['id'], $game_play_info['game_id']);
        ##奇数5
        $win_money_odd_5 = $this->GameRepository->calc_odd_5($game_play_info['id'], $game_play_info['game_id']);
        ##偶数
        $win_money_even = $this->GameRepository->calc_even($game_play_info['id'], $game_play_info['game_id']);
        ##偶数0
        $win_money_even_0 = $this->GameRepository->calc_even_0($game_play_info['id'], $game_play_info['game_id']);
        ##幸运
        $win_money_lucky = $this->GameRepository->calc_even_luck($game_play_info['id'], $game_play_info['game_id']);

        $result_array = [];
        $result_win_array = [];

        ##依次计算0-9每个结果的中奖金额
        ## 0 => 偶数 幸运 0
        $calc_0 = $this->GameRepository->Calc_0($game_play_info['id'], $game_play_info['game_id']);
        $win_money_0 = $win_money_even_0 + $win_money_lucky + (int)$calc_0['win_money'];
        $result_array[] = 0;
        $temp_win_money = $win_money_0;
//        echo "++ 0<====>" . $win_money_0 . "++";
        ## 1 => 奇数 1
        $calc_1 = $this->GameRepository->Calc_1($game_play_info['id'], $game_play_info['game_id']);
        $win_money_1 = $win_money_odd + (int)$calc_1['win_money'];
        $res = $this->calculateResult($temp_win_money, $win_money_1, $can_donate_money, $result_array, 1);
        $result_array = $res['result_array'];
        $temp_win_money = $res['temp_win_money'];
        ## 2 => 偶数 2
        $calc_2 = $this->GameRepository->Calc_2($game_play_info['id'], $game_play_info['game_id']);
        $win_money_2 = $win_money_even + (int)$calc_2['win_money'];
        $res = $this->calculateResult($temp_win_money, $win_money_2, $can_donate_money, $result_array, 2);
        $result_array = $res['result_array'];
        $temp_win_money = $res['temp_win_money'];

        ## 3 => 奇数 3
        $calc_3 = $this->GameRepository->Calc_3($game_play_info['id'], $game_play_info['game_id']);
        $win_money_3 = $win_money_odd + (int)$calc_3['win_money'];
        $res = $this->calculateResult($temp_win_money, $win_money_3, $can_donate_money, $result_array, 3);
        $result_array = $res['result_array'];
        $temp_win_money = $res['temp_win_money'];

        ## 4 => 偶数 4
        $calc_4 = $this->GameRepository->Calc_4($game_play_info['id'], $game_play_info['game_id']);
        $win_money_4 = $win_money_even + (int)$calc_4['win_money'];
        $res = $this->calculateResult($temp_win_money, $win_money_4, $can_donate_money, $result_array, 4);
        $result_array = $res['result_array'];
        $temp_win_money = $res['temp_win_money'];

        ## 5 => 奇数_5 5 luck
        $calc_5 = $this->GameRepository->Calc_5($game_play_info['id'], $game_play_info['game_id']);
        $win_money_5 = $win_money_odd_5 + (int)$calc_5['win_money'] + $win_money_lucky;
        $res = $this->calculateResult($temp_win_money, $win_money_5, $can_donate_money, $result_array, 5);
        $result_array = $res['result_array'];
        $temp_win_money = $res['temp_win_money'];

        ## 6 => 偶数 6
        $calc_6 = $this->GameRepository->Calc_6($game_play_info['id'], $game_play_info['game_id']);
        $win_money_6 = $win_money_even + (int)$calc_6['win_money'];
        $res = $this->calculateResult($temp_win_money, $win_money_6, $can_donate_money, $result_array, 6);
        $result_array = $res['result_array'];
        $temp_win_money = $res['temp_win_money'];

        ## 7 => 奇数 7
        $calc_7 = $this->GameRepository->Calc_7($game_play_info['id'], $game_play_info['game_id']);
        $win_money_7 = $win_money_odd + (int)$calc_7['win_money'];
        $res = $this->calculateResult($temp_win_money, $win_money_7, $can_donate_money, $result_array, 7);
        $result_array = $res['result_array'];
        $temp_win_money = $res['temp_win_money'];

        ## 8 => 偶数 8
        $calc_8 = $this->GameRepository->Calc_8($game_play_info['id'], $game_play_info['game_id']);
        $win_money_8 = $win_money_even + (int)$calc_8['win_money'];
        $res = $this->calculateResult($temp_win_money, $win_money_8, $can_donate_money, $result_array, 8);
        $result_array = $res['result_array'];
        $temp_win_money = $res['temp_win_money'];

        ## 9 => 奇数 9
        $calc_9 = $this->GameRepository->Calc_9($game_play_info['id'], $game_play_info['game_id']);
        $win_money_9 = $win_money_odd + (int)$calc_9['win_money'];
        $res = $this->calculateResult($temp_win_money, $win_money_9, $can_donate_money, $result_array, 9);
        $result_array = $res['result_array'];
        $temp_win_money = $res['temp_win_money'];

        ##计算结果
        $n = count($result_array);
//        print_r($result_array);
        if($n == 1){
            $result = $result_array[0];
        }else{
            $idx = mt_rand(0, $n-1);
            $result = $result_array[$idx];
        }
        $win_money = $temp_win_money;

        return compact('result','win_money');
    }

    public function Ki_Executive_Prize($result, $play_id, $winmoney, $lostmoney, $type, $pt_money, $cur_betting_money){
        $data=$this->GameRepository->Get_Betting($play_id);
        if($data){
            foreach ($data as $val){
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
        }

        return $this->GameRepository->Ki_Play_Result_Entry($play_id, $result, $type, $winmoney,$lostmoney, $pt_money, $cur_betting_money);
    }

    public function ssc($play_id)
    {
        $Is_Executive_Prize=$this->GameRepository->Get_Info($play_id);
        if($Is_Executive_Prize<=0){
            return false;
        }
        //单局杀率判定
        $system=$this->GameRepository->Get_System();
        $kill_rate=$system->one_kill;//获得杀率
        $rand=rand(0,100);//随机数
        ($kill_rate*100)<=$rand?$isWin=2:$isWin=1;//判断本局输还是赢
        //整体杀率判定
        $date_money=$this->GameRepository->Get_Date_Money();
        $new_money_sum=$this->GameRepository->Get_New_Sum_Money();
        //是否开启天杀率控制
        if($system->is_date_kill==1){
            //获得当天实际整体杀率
            if($new_money_sum['c_money']>0){
                $date_sj_kill=($new_money_sum['s_money']-$new_money_sum['y_money'])/$new_money_sum['c_money'];
                if($date_sj_kill<=0){
                    $isWin=1;
                }else{
                    $p_kill=$date_sj_kill-$system->date_kill;
                    if($p_kill>0.05){
                        $isWin=2;
                    }else{
                        $isWin=1;
                    }
                }
            }

        }
        //echo "单局杀率为".$kill_rate."天杀率为".$system->date_kill."单句输赢为".$rand."天判定为".$isWin."整体真实杀率为".$date_sj_kill;


        $arr=array();
        $b_money=$this->GameRepository->Get_Betting_Sum($play_id);
        for($i=0;$i<10;$i++){
            $number_kj=$this->getResult();
            $result=$number_kj['str'];//随机获取一个开奖号码
            $result1=$number_kj['str1'];//存入数据库使用的开奖号码
            $this->lostmoney=0;//输的钱
            $this->winmoney=0;//赢得钱
            $this->winmoney1=0;
            $this->Calculation($play_id,$i);
            $arr[$i]['lostmoney']=$this->lostmoney;
            $arr[$i]['winmoney']=$this->winmoney;
            if($b_money==0){
                $this->Executive_Prize($play_id,$result,3,$this->winmoney,$this->lostmoney,$b_money,$result1);
                break;
            }
        }
        if($b_money==0){
            return true;
            exit;
        }
        $sd=array();
        for($i=0;$i<count($arr);$i++){
            $sd[$i]=$b_money-$arr[$i]["winmoney"];
        }
        arsort( $sd,1);
        $new_count=$this->GameRepository->Get_Betting_Usering($play_id);

        $ar_new=array();
        $ar_new["0"] =0;
        $ar_new["1"] =0;
        $ar_new["2"] =0;
        $ar_new["3"] =0;
        $ar_new["4"] =0;
        $ar_new["5"] =0;
        $ar_new["6"] =0;
        $ar_new["7"] =0;
        $ar_new["8"] =0;
        $ar_new["9"] =0;
        foreach ($new_count as $key => $value){
            if($value['name']=="0"){
                $ar_new["0"]+=$value['count'];
            }elseif ($value['name']=="1"){
                $ar_new["1"]+=$value['count'];
            }elseif ($value['name']=="2"){
                $ar_new["2"]+=$value['count'];
            }elseif ($value['name']=="3"){
                $ar_new["3"]+=$value['count'];
            }elseif ($value['name']=="4"){
                $ar_new["4"]+=$value['count'];
            }elseif ($value['name']=="5"){
                $ar_new["5"]+=$value['count'];
            }elseif ($value['name']=="6"){
                $ar_new["6"]+=$value['count'];
            }elseif ($value['name']=="7"){
                $ar_new["7"]+=$value['count'];
            }elseif ($value['name']=="8"){
                $ar_new["8"]+=$value['count'];
            }elseif ($value['name']=="9"){
                $ar_new["9"]+=$value['count'];
            }elseif ($value['name']=="奇数"){
                $ar_new["1"]+=$value['count'];
                $ar_new["3"]+=$value['count'];
                $ar_new["5"]+=$value['count'];
                $ar_new["7"]+=$value['count'];
                $ar_new["9"]+=$value['count'];
            }elseif ($value['name']=="偶数"){
                $ar_new["0"]+=$value['count'];
                $ar_new["2"]+=$value['count'];
                $ar_new["4"]+=$value['count'];
                $ar_new["6"]+=$value['count'];
                $ar_new["8"]+=$value['count'];
            }elseif ($value['name']=="幸运"){
                $ar_new["0"]+=$value['count'];
                $ar_new["5"]+=$value['count'];

            }
        }
        $kaijiang=0;
        arsort( $ar_new,1);
        foreach ($ar_new as $key => $value){
            if($sd[$key]>0){
                    $kaijiang=$key;
                break;
            }
        }
        $shu_kaijiang=0;
        asort($ar_new,1);

        foreach ($ar_new as $key => $value){
            if($sd[$key]<0){
                $shu_kaijiang=$key;
                break;
            }
        }
//        echo "最优开奖号码".$kaijiang;
        if($isWin==1){
                    //执行开奖
                    $this->Executive_Prize($play_id,$kaijiang,$isWin,$arr[$kaijiang]["winmoney"],$arr[$kaijiang]["lostmoney"],$b_money,$kaijiang);
            return true;
        }else if($isWin==2){
                    //执行开奖
                    $this->Executive_Prize($play_id,$shu_kaijiang,$isWin,$arr[$shu_kaijiang]["winmoney"],$arr[$shu_kaijiang]["lostmoney"],$b_money,$shu_kaijiang);
            return true;
        }
//        var_dump($ar_new);
//        dd($sd);
//        dd($arr);

    }
    public function ssc_sd($play_id,$prize_number)
    {
        $Is_Executive_Prize=$this->GameRepository->Get_Info($play_id);
        if($Is_Executive_Prize<=0){
            return false;
        }
        $arr=array();
        $b_money=$this->GameRepository->Get_Betting_Sum($play_id);
        $result=$prize_number;//随机获取一个开奖号码
        $result1=$prize_number;//存入数据库使用的开奖号码
        $this->lostmoney=0;//输的钱
        $this->winmoney=0;//赢得钱
        $this->winmoney1=0;
        $this->Calculation($play_id,$prize_number);
        if($b_money==0){
            $this->Executive_Prize($play_id,$result,3,$this->winmoney,$this->lostmoney,$b_money,$result1);
            return true;
            exit;
        }

        if($b_money==0){
            return true;
            exit;
        }
        if($b_money>$this->lostmoney){
            $isWin=1;
        }else if($b_money<$this->lostmoney){
            $isWin=2;
        }else{
            $isWin=3;
        }
        //执行开奖
        $this->Executive_Prize($play_id,$prize_number,$isWin,$this->winmoney,$this->lostmoney,$b_money,$prize_number);
        return true;


    }
    public function ssc_se($play_id)
    {
        $Is_Executive_Prize=$this->GameRepository->Get_Info($play_id);
        if($Is_Executive_Prize<=0){
            return false;
        }
        $arr=array();
        $b_money=$this->GameRepository->Get_Betting_Sum($play_id);

        for($i=0;$i<10;$i++){

            $this->lostmoney=0;//输的钱
            $this->winmoney=0;//赢得钱
            $this->winmoney1=0;
            $this->Calculation($play_id,$i);
            $arr[$i]['lostmoney']=$this->lostmoney;
            $arr[$i]['winmoney']=$this->winmoney;

        }

        $sd=array();
        for($i=0;$i<count($arr);$i++){
            $sd[$i]=$b_money-$arr[$i]["winmoney"];
        }
        arsort( $sd,1);
        $new_count=$this->GameRepository->Get_Betting_Usering($play_id);

        $ar_new=array();
        $ar_new["0"] =0;
        $ar_new["1"] =0;
        $ar_new["2"] =0;
        $ar_new["3"] =0;
        $ar_new["4"] =0;
        $ar_new["5"] =0;
        $ar_new["6"] =0;
        $ar_new["7"] =0;
        $ar_new["8"] =0;
        $ar_new["9"] =0;
        foreach ($new_count as $key => $value){
            if($value['name']=="0"){
                $ar_new["0"]+=$value['count'];
            }elseif ($value['name']=="1"){
                $ar_new["1"]+=$value['count'];
            }elseif ($value['name']=="2"){
                $ar_new["2"]+=$value['count'];
            }elseif ($value['name']=="3"){
                $ar_new["3"]+=$value['count'];
            }elseif ($value['name']=="4"){
                $ar_new["4"]+=$value['count'];
            }elseif ($value['name']=="5"){
                $ar_new["5"]+=$value['count'];
            }elseif ($value['name']=="6"){
                $ar_new["6"]+=$value['count'];
            }elseif ($value['name']=="7"){
                $ar_new["7"]+=$value['count'];
            }elseif ($value['name']=="8"){
                $ar_new["8"]+=$value['count'];
            }elseif ($value['name']=="9"){
                $ar_new["9"]+=$value['count'];
            }elseif ($value['name']=="奇数"){
                $ar_new["1"]+=$value['count'];
                $ar_new["3"]+=$value['count'];
                $ar_new["5"]+=$value['count'];
                $ar_new["7"]+=$value['count'];
                $ar_new["9"]+=$value['count'];
            }elseif ($value['name']=="偶数"){
                $ar_new["0"]+=$value['count'];
                $ar_new["2"]+=$value['count'];
                $ar_new["4"]+=$value['count'];
                $ar_new["6"]+=$value['count'];
                $ar_new["8"]+=$value['count'];
            }elseif ($value['name']=="幸运"){
                $ar_new["0"]+=$value['count'];
                $ar_new["5"]+=$value['count'];

            }
        }
        $kaijiang=0;
        arsort( $ar_new,1);
        foreach ($ar_new as $key => $value){
            if($sd[$key]>0){
                $kaijiang=$key;
                break;
            }
        }
        $shu_kaijiang=0;
        asort($ar_new,1);

        foreach ($ar_new as $key => $value){
            if($sd[$key]<0){
                $shu_kaijiang=$key;
                break;
            }
        }
        $result_data['msg']="系统推荐最优开奖号码".$kaijiang;
        $result_data['new_user']=$ar_new;
        $result_data['b_money']=$b_money;
        $result_data['sy']=$sd;

        return $result_data;
    }
    public function Calculation($play_id,$result){
        $Calculation=(int)$result;
        //获取10位下用户下注项分组统计总金额
        $data=$this->GameRepository->GroupBy_W($play_id);

        if($data){
            foreach ($data as $val){
                if($val->game_c_x_id==50){
                    if($Calculation==0){
                        //中奖
                        $this->winmoney +=($val->money*9);
                        $this->winmoney1 +=$val->money;
                        continue;
                    }else{
                        //不中奖
                        $this->lostmoney +=$val->money;
                        continue;
                    }

                }else if($val->game_c_x_id==13){
                    if($Calculation==1){
                        //中奖
                        $this->winmoney +=($val->money*9);
                        $this->winmoney1 +=$val->money;
                        continue;
                    }else{
                        //不中奖
                        $this->lostmoney +=$val->money;
                        continue;
                    }

                }else if($val->game_c_x_id==14){
                    if($Calculation==2){
                        //中奖
                        $this->winmoney +=($val->money*9);
                        $this->winmoney1 +=$val->money;
                        continue;
                    }else{
                        //不中奖
                        $this->lostmoney +=$val->money;
                        continue;
                    }

                }else if($val->game_c_x_id==15){
                    if($Calculation==3){
                        //中奖
                        $this->winmoney +=($val->money*9);
                        $this->winmoney1 +=$val->money;
                        continue;
                    }else{
                        //不中奖
                        $this->lostmoney +=$val->money;
                        continue;
                    }

                }else if($val->game_c_x_id==16){
                    if($Calculation==4){
                        //中奖
                        $this->winmoney +=($val->money*9);
                        $this->winmoney1 +=$val->money;
                        continue;
                    }else{
                        //不中奖
                        $this->lostmoney +=$val->money;
                        continue;
                    }

                }else if($val->game_c_x_id==17){
                    if($Calculation==5){
                        //中奖
                        $this->winmoney +=($val->money*9);
                        $this->winmoney1 +=$val->money;
                        continue;
                    }else{
                        //不中奖
                        $this->lostmoney +=$val->money;
                        continue;
                    }

                }else if($val->game_c_x_id==18){
                    if($Calculation==6){
                        //中奖
                        $this->winmoney +=($val->money*9);
                        $this->winmoney1 +=$val->money;
                        continue;
                    }else{
                        //不中奖
                        $this->lostmoney +=$val->money;
                        continue;
                    }

                }else if($val->game_c_x_id==19){
                    if($Calculation==7){
                        //中奖
                        $this->winmoney +=($val->money*9);
                        $this->winmoney1 +=$val->money;
                        continue;
                    }else{
                        //不中奖
                        $this->lostmoney +=$val->money;
                        continue;
                    }

                }else if($val->game_c_x_id==20){
                    if($Calculation==8){
                        //中奖
                        $this->winmoney +=($val->money*9);
                        $this->winmoney1 +=$val->money;
                        continue;
                    }else{
                        //不中奖
                        $this->lostmoney +=$val->money;
                        continue;
                    }

                }else if($val->game_c_x_id==21){
                    if($Calculation==9){
                        //中奖
                        $this->winmoney +=($val->money*9);
                        $this->winmoney1 +=$val->money;
                        continue;
                    }else{
                        //不中奖
                        $this->lostmoney +=$val->money;
                        continue;
                    }

                }else if($val->game_c_x_id==22){
                    if($Calculation==1 || $Calculation==3 || $Calculation==7 || $Calculation==9 || $Calculation==5 ){
                        //中奖
                        if($Calculation==5){
                            $this->winmoney +=($val->money*1.5);
                            $this->winmoney1 +=$val->money;
                            continue;
                        }else{
                            $this->winmoney +=($val->money*2);
                            $this->winmoney1 +=$val->money;
                            continue;
                        }
                    }else{
                        //不中奖
                        $this->lostmoney +=$val->money;
                        continue;
                    }

                }else if($val->game_c_x_id==23){
                    if($Calculation==2 || $Calculation==4 || $Calculation==6 || $Calculation==8 || $Calculation==0){
                        //中奖
                        if($Calculation==0){
                            $this->winmoney +=($val->money*1.5);
                            $this->winmoney1 +=$val->money;
                            continue;
                        }else{
                            $this->winmoney +=($val->money*2);
                            $this->winmoney1 +=$val->money;
                            continue;
                        }

                    }else{
                        //不中奖
                        $this->lostmoney +=$val->money;
                        continue;
                    }

                }else if($val->game_c_x_id==24){
                    if($Calculation==0 || $Calculation==5){
                        //中奖
                        $this->winmoney +=($val->money*4.5);
                        $this->winmoney1 +=$val->money;
                        continue;
                    }else{
                        //不中奖
                        $this->lostmoney +=$val->money;
                        continue;
                    }

                }
            }

        }
    }

    public function Executive_Prize($play_id,$result,$isWin,$winmoney,$lostmoney,$winmoney1,$result1){
        $data=$this->GameRepository->Get_Betting($play_id);
        if($data){
            foreach ($data as $val){
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

        }
        return $this->GameRepository->Play_Result_Entry($play_id,$result,$isWin,$winmoney,$lostmoney,$winmoney1,$result1);


    }
}
