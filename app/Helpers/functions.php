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
