<?php


namespace App\Services\Game;


use App\Jobs\GameSettlement;
use App\Repositories\Game\GameRepository;
use App\Repositories\Api\UserRepository;
use App\Services\Game\Ssc_TwoService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Services\Game\SscService;
use App\Services\Game\Ssc_FourService;
use App\Services\Game\Ssc_ThreeService;
use Illuminate\Http\Request;

class GameService
{
    protected $GameRepository;
    protected $UserRepository;
    protected $SscService;
    protected $Ssc_FourService;
    protected $Ssc_TwoService;
    protected $Ssc_ThreeService;


    public function __construct(
        SscService $SscService,
        GameRepository $GameRepository,
        UserRepository $UserRepository,
        Ssc_FourService $Ssc_FourService,
        Ssc_TwoService $Ssc_TwoService,
        Ssc_ThreeService $Ssc_ThreeService)
    {
        $this->GameRepository = $GameRepository;
        $this->UserRepository = $UserRepository;
        $this->SscService = $SscService;
        $this->Ssc_FourService = $Ssc_FourService;
        $this->Ssc_TwoService = $Ssc_TwoService;
        $this->Ssc_ThreeService = $Ssc_ThreeService;

    }

    public function Generate_Number()
    {
        $this->GameRepository->Generate_Number();
    }

    public function Game_Start($request)
    {
        $token = $request->header("token");
        $data = $request->all();
        $token = urldecode($token);
        $user = explode("+", Crypt::decrypt($token));
        $user_id = $user[0];
        return $this->GameRepository->Game_Start($request->input("id"), $user_id);
    }

    //用户投注
    public function Betting($request)
    {
        $token = $request->header("token");
        $data = $request->all();
        $token = urldecode($token);
        $user = explode("+", Crypt::decrypt($token));
        $user_id = $user[0];

        //判断下注时间是否在允许投注时间内
        $game_play = $this->GameRepository->Get_Game_Play_ById($data['game_p_id']);
        $time = time();

        if ($time >= $game_play->start_time && $time <= $game_play->end_time) {

        } else {

            return false;
        }
        //判断用户余额是否大于投注金额
        $user_info = $this->UserRepository->findByIdUser($user_id);

        if ($data["money"] > $user_info->balance) {
            return false;
        }
        //进行投注
        if ($this->GameRepository->Betting($user_info, $data)) {
            $this->CalculateRevenue($user_info, $data["money"]);
            return true;
        }
    }

    /**
     * 计算收益并入库
     * @param $user
     * @param $money
     */
    public function CalculateRevenue($user, $money)
    {
        // 计算平台收益，一级代理人收益，二级代理人收益
        $serviceCharge = $money * 0.03;
        $oneCharge = $serviceCharge * 0.3;
        $twoCharge = $serviceCharge * 0.2;
        $platformCharge = $serviceCharge - $oneCharge - $twoCharge;
        DB::beginTransaction();
        try {
            // 将一级，二级代理人收益添加到数据库
            $one = $this->UserRepository->findByIdUser($user->one_recommend_id);
            $two = $this->UserRepository->findByIdUser($user->two_recommend_id);
            $oneCondition = ["id" => $user->one_recommend_id, "one_commission" => $one->one_commission + $oneCharge, "commission" => $one->commission + $oneCharge];
            $twoCondition = ["id" => $user->two_recommend_id, "two_commission" => $two->two_commission + $twoCharge, "commission" => $two->commission + $twoCharge];
            $this->UserRepository->updateAgentMoney($oneCondition);
            $this->UserRepository->updateAgentMoney($twoCondition);

            // 更改平台收入
            $system = $this->UserRepository->findSystemCharge();
            $this->UserRepository->updateSystemCharge($system->platform_charge + $platformCharge);

            // 将收入记录入库
            $oneChargeLog = ["betting_user_id" => $user->id, "charge_user_id" => $user->one_recommend_id, "type" => 1, "money" => $oneCharge, "create_time" => time()];
            $twoChargeLog = ["betting_user_id" => $user->id, "charge_user_id" => $user->two_recommend_id, "type" => 2, "money" => $twoCharge, "create_time" => time()];
            $systemChargeLog = ["betting_user_id" => $user->id, "charge_user_id" => 0, "type" => 0, "money" => $platformCharge, "create_time" => time()];
            $this->UserRepository->addChargeLogs($oneChargeLog);
            $this->UserRepository->addChargeLogs($twoChargeLog);
            $this->UserRepository->addChargeLogs($systemChargeLog);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    //获取用户投注列表
    public function Betting_List($request)
    {
        $limit = $request->input('limit');
        $page = $request->input('page');
        $token = $request->header("token");
        $data = $request->all();
        $token = urldecode($token);
        $user = explode("+", Crypt::decrypt($token));
        $user_id = $user[0];

        return $this->GameRepository->Betting_List($user_id, $limit, ($page - 1) * $limit);
    }

    //获取游戏开奖历史列表
    public function Game_List($request)
    {
        $limit = $request->input('limit');
        $page = $request->input('page');
        $game_id = $request->input('game_id');
        return $this->GameRepository->Game_List($game_id, $limit, ($page - 1) * $limit);
    }

    /*结算定时任务
     *获取可结算的期数添加入消费队列
     */
    public function Settlement_Queue()
    {

//        $this->SscService->ssc(1472);
//        exit;
        $data = $this->GameRepository->Get_Settlement();
        if (count($data) > 0) {
            foreach ($data as $val) {
                GameSettlement::dispatch($val->id, $val->game_id)->onQueue('Settlement_Queue');
            }
            return true;
        }


    }

    public function Settlement_Queue_Test()
    {

        $this->Ssc_FourService->ssc_se(35253);
        exit;

    }


    public function Get_Prize_Opening_Data($game_id, $play_id)
    {

        if ($game_id == 1) {
            $data = $this->SscService->ssc_se($play_id);
        } else if ($game_id == 2) {
            $data = $this->Ssc_TwoService->ssc_se($play_id);
        } else if ($game_id == 3) {
            $data = $this->Ssc_ThreeService->ssc_se($play_id);
        } else if ($game_id == 4) {
            $data = $this->Ssc_FourService->ssc_se($play_id);
        }
        return $data;
    }


}