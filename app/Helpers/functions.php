<?php

function search_filter($str){
    $str = str_replace('*','',$str);
    $str = str_replace('%','',$str);
    $str = str_replace('_','',$str);
    return str_filter($str);
}

function str_filter($str){
    return addslashes(strip_tags(trim($str)));
}

function hide($str, $start, $len){
    return substr_replace($str,str_repeat("*",$len),$start,$len);
}

function day_start(){
    return strtotime(date('Y-m-d') . ' 00:00:00');
}

function day_end(){
    return strtotime(date('Y-m-d') . ' 23:59:59');
}

function makeInviteRelation($relation, $user_id){
    return '-' . trim($user_id . '-' . trim($relation,'-'),'-') . '-';
}

function makeModel($where, $model){
    foreach($where as $key => $item){
        switch ($item[0]){
            case '=':
                $model = $model->where($key, $item[1]);
                break;
            case 'BETWEEN':
                $model = $model->whereBetween($key, $item[1]);
                break;
            case 'in':
                $model = $model->whereIn($key, $item[1]);
                break;
            case 'IntegerInRaw':
                $model = $model->whereIntegerInRaw($key,$item[1]);
                break;
            case 'like':
                $model = $model->where($key, 'like', $item[1]);
                break;
        }
    }
    return $model;
}

function rpNBSP($html)
{
    return str_replace('&nbsp;',' ', $html);
}

function getHtml($html)
{
    return rpNBSP(htmlspecialchars_decode(htmlspecialchars_decode($html)));
}

function redisHGetALl($key, $arrKey=[]):array
{
    $data = \Illuminate\Support\Facades\Redis::hgetall($key);
    foreach($data as $k => &$val){
        if(in_array($k, $arrKey))$val = json_decode($val,true);
    }
    return $data;
}

function redisHSetAll($key, $array)
{
    foreach($array as $k => $arr){
        if(is_array($arr))$arr = json_encode($arr);
        $data[$k] = \Illuminate\Support\Facades\Redis::hset($key, $k, $arr);
    }
}
