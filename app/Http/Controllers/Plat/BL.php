<?php


namespace App\Http\Controllers\Plat;


use App\Http\Controllers\Controller;
use App\Libs\Games\WDYY\Client;
use Illuminate\Support\Facades\Validator;

class BL extends Controller
{

    public function blReturn($retCode, $data=[], $msg='')
    {
        return response()->json(
            [
                "retCode" => $retCode,
                "data" => $data,
//                "msg" => $msg
            ]
        )->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    protected $Client;

    public function __construct
    (
        Client $client
    )
    {
        $this->Client = $client;
    }

    public function balance()
    {
        $validator = Validator::make(request()->input(), [
            'action' => 'required',
            'token' => 'required',
            'sign' => 'required',
        ]);
        if($validator->fails())
        {
            return $this->blReturn(1);
        }
        $this->Client->balanceHandle();
        return $this->blReturn($this->Client->_data['retCode'],$this->Client->_data['data']);
    }

    public function userinfo()
    {
        $validator = Validator::make(request()->input(), [
            'token' => 'required',
            'sign' => 'required',
        ]);
        if($validator->fails())
        {
            return $this->blReturn(1,[], $validator->errors()->first());
        }
        $this->Client->userInfo();
        return $this->blReturn($this->Client->_data['retCode'],$this->Client->_data['data'],$this->Client->_msg);
    }

}
