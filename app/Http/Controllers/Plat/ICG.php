<?php
namespace App\Http\Controllers\Plat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Libs\Aes;
use App\Libs\Games\Icg\IcgLog;
use Illuminate\Support\Facades\Crypt;

class ICG extends Controller{
    private $Icg;

    public function __construct
    (
        IcgLog $Icg
    )
    {
        $this->Icg = $Icg;
    }

    //icg平台用户入金
    public function ICGUserTopScores(Request $request){
        $money = $request->input("p");//要上分的金额
        $money = json_decode(aesDecrypt($money),true);
        $money = $money["money"];
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));

        $list = $this->Icg->ICGUserTopScores($data[0],$money);
        if(!$list){
            return [
                "code" => 404,
                "msg" => "link error",
            ];
        }
        return $list;
    }

    //icg平台用户出金
    public function ICGUserLowerScores(Request $request){
        $money = $request->input("p");//要下分的金额
        $money = json_decode(aesDecrypt($money),true);
        $money = $money["money"];
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        //调用下分
        $list = $this->Icg->ICGUserLowerScores($data[0],$money);
        if(!$list){
            return [
                "code" => 404,
                "msg" => "link error",
            ];
        }
        return $list;
    }

    //icg平台查询用户余额
    public function IcgQueryScore(Request $request){
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        //调用查询接口
        $list = $this->Icg->IcgQueryScore($data[0]);
        if(!$list){
            return [
                "code" => 404,
                "msg" => "link error",
            ];
        }
        return $list;
    }

}
