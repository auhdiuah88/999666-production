<?php


namespace App\Http\Controllers\Game;


use App\Http\Controllers\Controller;
use App\Services\Game\GameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class GameController extends Controller
{

    protected $GameService;

    public function __construct(GameService $GameService)
    {
        $this->GameService = $GameService;
    }

    public function Game_Start(Request $request)
    {
        $rules = [
            "id" => "required|max:5|integer",
        ];
        $massages = [
            "id.required" => "游戏不能为空",
            "id.max" => "游戏必须小于等于5",
            "id.integer" => "游戏必须为整型",
        ];
        $validator = Validator::make($request->all(), $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $data = $this->GameService->Game_Start($request);
        if ($data) {
            return $this->AppReturn(200, '成功', $data);
        } else {
            return $this->AppReturn(412, '封盘中');
        }

    }

    public function Settlement_Queue()
    {
        $this->GameService->Settlement_Queue();
    }

    public function Settlement_Queue_Test()
    {
        $this->GameService->Settlement_Queue_Test();
    }

    public function Get_Prize_Opening_Data(Request $request)
    {

        $rules = [
            "game_id" => "required|max:5|integer",
            "game_play_id" => "required|integer",


        ];
        $massages = [
            "game_id.required" => "游戏不能为空",
            "game_id.max" => "游戏必须小于等于5",
            "game_id.integer" => "游戏必须为整型",
            "game_play_id.integer" => "期数必须为整型",
            "game_play_id.required" => "期数不能为空",
        ];
        $validator = Validator::make($request->all(), $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $data = $this->GameService->Get_Prize_Opening_Data($request->input("game_id"), $request->input("game_play_id"));

        if ($data) {
            return $this->AppReturn(200, '查询成功', $data);
        } else {
            return $this->AppReturn(413, '失败');
        }

    }
    public function Sd_Prize_Opening(Request $request)
    {
        $rules = [
            "number" => "required|lte:9|integer|gte:0",
            "game_play_id" => "required|integer",
        ];
        $massages = [
            "number.required" => "开奖号不能为空",
            "number.max" => "开奖号必须小于等于9",
            "number.integer" => "开奖号必须为整型",
            "game_play_id.integer" => "期数必须为整型",
            "game_play_id.required" => "期数不能为空",
        ];
        $validator = Validator::make($request->all(), $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $data = $this->GameService->Sd_Prize_Opening($request->input("number"), $request->input("game_play_id"));

        if ($data) {
            return $this->AppReturn(200, '手动开奖设置成功', $data);
        } else {
            return $this->AppReturn(413, '该期即将进入或已进入开奖队列，无法执行手动开奖');
        }

    }



    public function Betting(Request $request)
    {
//        return $this->AppReturn(413, 'System upgrading');
        $rules = [
            "game_id" => "required|max:5|integer",
            "game_p_id" => "required|integer",
            "game_c_x_id" => "required",
            "money" => "required|gte:10",

        ];
        $massages = [
            "game_id.required" => "游戏不能为空",
            "game_id.max" => "游戏必须小于等于5",
            "game_id.integer" => "游戏必须为整型",
            "game_p_id.integer" => "期数必须为整型",
            "game_p_id.required" => "期数不能为空",
            "game_c_x_id.required" => "下注项不能为空",
            "money.required" => "金额不能为空",
        ];
        $validator = Validator::make($request->all(), $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $balance = $this->GameService->Betting($request);
        if ($balance) {
            $data['balance']=$balance;
            return $this->AppReturn(200, "Betting Success",$data);
        } else {
            return $this->AppReturn(413, 'Insufficient balance or abnormal network');
        }

    }

    public function Betting_List(Request $request)
    {
        $rules = [
            "limit" => "required",
            "page" => "required",
            "game_id" => ["required", "integer", "gt:0"]
        ];
        $massages = [
            "limit.required" => "条数不能为空",
            "page.required" => "页数不能为空",
        ];
        $validator = Validator::make($request->all(), $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $data = $this->GameService->Betting_List($request);
        return $this->AppReturn(200, '成功', $data);

    }

    public function Game_List(Request $request)
    {
        $rules = [
            "limit" => "required",
            "page" => "required",
            "game_id" => "required",
        ];
        $massages = [
            "limit.required" => "条数不能为空",
            "page.required" => "页数不能为空",
            "game_id.required" => "游戏id不能为空",
        ];
        $validator = Validator::make($request->all(), $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $data = $this->GameService->Game_List($request);
        return $this->AppReturn(200, '成功', $data);

    }

    public function Open_Game_Betting_SD()
    {
        $rules = [
            "game_p_id" => 'required|integer|gte:1',
        ];
        $validator = Validator::make(request()->input(), $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $res = $this->GameService->Open_Game_Betting_SD();
        if($res === true)
        {
            return $this->AppReturn(200, '操作成功');
        }else{
            return $this->AppReturn(200, $res);
        }
    }

    public function open_betting()
    {
        $game_play_id = request('id',0);
        ##获取该期数下面20期未开奖的
        $this->GameService->open__betting($game_play_id);
        return $this->AppReturn(200, '操作成功');
    }

}
