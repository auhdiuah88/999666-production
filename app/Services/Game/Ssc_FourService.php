<?php


namespace App\Services\Game;

use App\Repositories\Game\GameRepository;
use App\Repositories\Game\SscRepository;
use Illuminate\Support\Facades\Redis;

class Ssc_FourService
{
    protected $SscRepository;
    protected $winmoney;
    protected $lostmoney;
    protected $winmoney1;
    protected $GameRepository;
    protected $REDIS_LOGIN="REDIS_ZONG_SHALV";

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
        ($kill_rate*100)<=$rand?$isWin=1:$isWin=2;//判断本局输还是赢
        //整体杀率判定
        $date_money=$this->GameRepository->Get_Date_Money();
        //是否开启天杀率控制
        if($system->is_date_kill==1){
            //获得当天实际整体杀率
            if($date_money->b_money>0){
                $date_sj_kill=($date_money->pt_money-$date_money->pt_s_money)/$date_money->b_money;
                $p_kill=$date_sj_kill-$system->date_kill;
                if($p_kill>0.05){
                    $isWin=2;
                }else{
                    $isWin=1;
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
        foreach ($ar_new as $key => $value){
            if($sd[$key]==0){
                $ping_kaijiang=$key;
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
$isWin=2;
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
            if($sd[$key]>=0){
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
                if($val->game_c_x_id==52){
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

                }else if($val->game_c_x_id==37){
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

                }else if($val->game_c_x_id==38){
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

                }else if($val->game_c_x_id==39){
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

                }else if($val->game_c_x_id==40){
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

                }else if($val->game_c_x_id==41){
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

                }else if($val->game_c_x_id==42){
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

                }else if($val->game_c_x_id==43){
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

                }else if($val->game_c_x_id==44){
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

                }else if($val->game_c_x_id==45){
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

                }else if($val->game_c_x_id==46){
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

                }else if($val->game_c_x_id==47){
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

                }else if($val->game_c_x_id==48){
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

        }
        return $this->GameRepository->Play_Result_Entry($play_id,$result,$isWin,$winmoney,$lostmoney,$winmoney1,$result1);


    }
}
