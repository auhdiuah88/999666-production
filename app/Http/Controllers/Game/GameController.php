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



    public function Betting(Request $request)
    {

        $rules = [
            "game_id" => "required|max:5|integer",
            "game_p_id" => "required|integer",
            "game_c_x_id" => "required",
            "money" => "required",

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
        $data = $this->GameService->Betting($request);
        if ($data) {
            return $this->AppReturn(200, '投注成功');
        } else {
            return $this->AppReturn(413, '余额不足或不在该期允许投注时间内');
        }

    }

    public function Betting_List(Request $request)
    {
        $rules = [
            "limit" => "required",
            "page" => "required",
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


}