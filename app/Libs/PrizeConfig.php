<?php


namespace App\Libs;


class PrizeConfig
{

    protected static $config = [
        '8.5' => [
            '0_5' => 1.49,
            'lucky' => 4.49,
            'number' => 8.99,
            'odd_even' => 1.99
        ],
        '8' => [
            '0_5' => 1.48,
            'lucky' => 4.48,
            'number' => 8.98,
            'odd_even' => 1.98
        ],
        '7' => [
            '0_5' => 1.47,
            'lucky' => 4.47,
            'number' => 8.97,
            'odd_even' => 1.97
        ],
        '6' => [
            '0_5' => 1.46,
            'lucky' => 4.46,
            'number' => 8.96,
            'odd_even' => 1.96
        ],
        '5' => [
            '0_5' => 1.45,
            'lucky' => 4.45,
            'number' => 8.95,
            'odd_even' => 1.95
        ],
        '4' => [
            '0_5' => 1.44,
            'lucky' => 4.44,
            'number' => 8.94,
            'odd_even' => 1.94
        ],
        '3' => [
            '0_5' => 1.43,
            'lucky' => 4.43,
            'number' => 8.93,
            'odd_even' => 1.93
        ],
        '2' => [
            '0_5' => 1.42,
            'lucky' => 4.42,
            'number' => 8.92,
            'odd_even' => 1.92
        ],
        '1' => [
            '0_5' => 1.41,
            'lucky' => 4.41,
            'number' => 8.91,
            'odd_even' => 1.91
        ],
        '0' => [
            '0_5' => 1.4,
            'lucky' => 4.4,
            'number' => 8.9,
            'odd_even' => 1.9
        ]
    ];

    public static function getRebateRate($rate, $val, $result): array
    {
        ##获取第一个k
        $k1 = self::getK1($rate);
        ##获取中奖类型
        $res = [];
        switch (intval($val->game_id)){
            case 1:
                $res = self::getRebateAndType_1($val, $result, $k1);
                break;
            case 2:
                $res = self::getRebateAndType_2($val, $result, $k1);
                break;
            case 3:
                $res = self::getRebateAndType_3($val, $result, $k1);
                break;
            case 4:
                $res = self::getRebateAndType_4($val, $result, $k1);
                break;
        }
        return $res;
    }

    protected static function getK1($rate): string
    {
        if($rate < 8.5){
            $rate = floor($rate);
            $k1 = (string)$rate;
        }else{
            $k1 = "8.5";
        }
        return $k1;
    }

    protected static function getRebateAndType_1($val, $result, $k1)
    {
        $type = 0;  //1赢  2输
        $rebate_rate = 1;  //倍数
        if($val->game_c_x_id==49){ //买0
            if($result==0){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==1){  //买1
            if($result==1){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==2){  //买2
            if($result==2){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==3){ //买3
            if($result==3){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==4){  //买4
            if($result==4){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==5){  //买5
            if($result==5){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==6){  //买6
            if($result==6){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==7){  //买7
            if($result==7){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==8){  //买8
            if($result==8){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==9){  //买9
            if($result==9){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==10){  //买奇数
            if($result==1 || $result==3 || $result==5 || $result==7 || $result==9){
                if($result==5){
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }
            }else{
                if($result==5){
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }

            }
        }else if($val->game_c_x_id==11){  //买偶数
            if($result==0 || $result==2 || $result==4 || $result==6 || $result==8){
                if($result==0){
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }
            }else{
                if($result==0){
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }
            }
        }else if($val->game_c_x_id==12){  //买幸运
            if($result==0 || $result==5 ){
                $type = 1;
                $rebate_rate = self::$config[$k1]['lucky'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['lucky'];
            }
        }
        return compact('type','rebate_rate');
    }

    protected static function getRebateAndType_2($val, $result, $k1)
    {
        $type = 0;  //1赢  2输
        $rebate_rate = 1;  //倍数
        if($val->game_c_x_id==50){ //买0
            if($result==0){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==13){  //买1
            if($result==1){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==14){  //买2
            if($result==2){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==15){ //买3
            if($result==3){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==16){  //买4
            if($result==4){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==17){  //买5
            if($result==5){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==18){  //买6
            if($result==6){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==19){  //买7
            if($result==7){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==20){  //买8
            if($result==8){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==21){  //买9
            if($result==9){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==22){  //买奇数
            if($result==1 || $result==3 || $result==5 || $result==7 || $result==9){
                if($result==5){
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }
            }else{
                if($result==5){
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }

            }
        }else if($val->game_c_x_id==23){  //买偶数
            if($result==0 || $result==2 || $result==4 || $result==6 || $result==8){
                if($result==0){
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }
            }else{
                if($result==0){
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }
            }
        }else if($val->game_c_x_id==24){  //买幸运
            if($result==0 || $result==5 ){
                $type = 1;
                $rebate_rate = self::$config[$k1]['lucky'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['lucky'];
            }
        }
        return compact('type','rebate_rate');
    }

    protected static function getRebateAndType_3($val, $result, $k1)
    {
        $type = 0;  //1赢  2输
        $rebate_rate = 1;  //倍数
        if($val->game_c_x_id==51){ //买0
            if($result==0){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==25){  //买1
            if($result==1){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==26){  //买2
            if($result==2){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==27){ //买3
            if($result==3){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==28){  //买4
            if($result==4){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==29){  //买5
            if($result==5){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==30){  //买6
            if($result==6){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==31){  //买7
            if($result==7){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==32){  //买8
            if($result==8){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==33){  //买9
            if($result==9){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==34){  //买奇数
            if($result==1 || $result==3 || $result==5 || $result==7 || $result==9){
                if($result==5){
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }
            }else{
                if($result==5){
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }

            }
        }else if($val->game_c_x_id==35){  //买偶数
            if($result==0 || $result==2 || $result==4 || $result==6 || $result==8){
                if($result==0){
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }
            }else{
                if($result==0){
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }
            }
        }else if($val->game_c_x_id==36){  //买幸运
            if($result==0 || $result==5 ){
                $type = 1;
                $rebate_rate = self::$config[$k1]['lucky'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['lucky'];
            }
        }
        return compact('type','rebate_rate');
    }

    protected static function getRebateAndType_4($val, $result, $k1)
    {
        $type = 0;  //1赢  2输
        $rebate_rate = 1;  //倍数
        if($val->game_c_x_id==52){ //买0
            if($result==0){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==37){  //买1
            if($result==1){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==38){  //买2
            if($result==2){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==39){ //买3
            if($result==3){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==40){  //买4
            if($result==4){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==41){  //买5
            if($result==5){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==42){  //买6
            if($result==6){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==43){  //买7
            if($result==7){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==44){  //买8
            if($result==8){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==45){  //买9
            if($result==9){
                $type = 1;
                $rebate_rate = self::$config[$k1]['number'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['number'];
            }
        }else if($val->game_c_x_id==46){  //买奇数
            if($result==1 || $result==3 || $result==5 || $result==7 || $result==9){
                if($result==5){
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }
            }else{
                if($result==5){
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }

            }
        }else if($val->game_c_x_id==47){  //买偶数
            if($result==0 || $result==2 || $result==4 || $result==6 || $result==8){
                if($result==0){
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 1;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }
            }else{
                if($result==0){
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['0_5'];
                }else{
                    $type = 2;
                    $rebate_rate = self::$config[$k1]['odd_even'];
                }
            }
        }else if($val->game_c_x_id==48){  //买幸运
            if($result==0 || $result==5 ){
                $type = 1;
                $rebate_rate = self::$config[$k1]['lucky'];
            }else{
                $type = 2;
                $rebate_rate = self::$config[$k1]['lucky'];
            }
        }
        return compact('type','rebate_rate');
    }

}
