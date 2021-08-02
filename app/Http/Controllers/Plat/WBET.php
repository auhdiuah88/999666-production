<?php
namespace App\Http\Controllers\Plat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Libs\Aes;
use App\Libs\Games\WBET\WbetLog;
use Illuminate\Support\Facades\Crypt;

class WBET extends Controller{
    private $Wbet;

    public function __construct
    (
        WbetLog $Wbet
    )
    {
        $this->Wbet = $Wbet;
    }

    //wbet平台用户入金
    public function WBETUserTopScores(Request $request){
        $money = $request->input("p");//要上分的金额
        $money = json_decode(aesDecrypt($money),true);
        $money = $money["money"];
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));

        $list = $this->Wbet->WBETUserTopScores($data[0],$money);
        if(!$list){
            return [
                "code" => 404,
                "msg" => "link error",
            ];
        }
        return $list;
    }

}
