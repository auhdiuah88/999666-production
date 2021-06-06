<?php


namespace App\Services\Game;


use App\Jobs\GameSettlement;
use App\Jobs\GameSettlement_Sd;
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
//        $data = $request->all();
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

        $game_c_x_id = $data['game_c_x_id'];
        $game_id = $data['game_id'];
        $right_game_id = $this->GameRepository->getGameIdByGameCXId($game_c_x_id);
        if($game_id != $right_game_id){
            return false;
        }

        //判断下注时间是否在允许投注时间内
        $game_play = $this->GameRepository->Get_Game_Play_ById($data['game_p_id']);
        $time = time();

//        if($game_play->is_status == 1) ##判断是否已手动开奖
//        {
//            return false;
//        }

        $game = $this->GameRepository->Get_Game_Config($game_id);
        $lock_time = $game->lock_time ?? 10;

        if ($time >= $game_play->start_time && $time <= $game_play->end_time - $lock_time) {

        } else {

            return false;
        }
        if($right_game_id != $game_play->game_id){
            return false;
        }
        //判断用户余额是否大于投注金额
        $user_info = $this->UserRepository->findByIdUser($user_id);

        if ($data["money"] > $user_info->balance) {
            return false;
        }
        if(env('PRIZE_TYPE',1) == 2){
            $prize_info = $this->Calc_Charge($user_info, $data["money"]);
        }else{
            $prize_info = [
                'serviceCharge' => $data["money"] * 0.03,
                'prize_arr' => []
            ];
        }
        //进行投注
        if ($balance=$this->GameRepository->Betting($user_info, $data, $prize_info)) {
            $this->CalculateRevenue($user_info, $data["money"], $prize_info['prize_arr']);
            return $balance;
        }
    }

    /**
     * 计算收益并入库
     * @param $user
     * @param $money
     * @param $prize_arr
     */
    public function CalculateRevenue($user, $money, $prize_arr)
    {
        $PRIZE_TYPE = env('PRIZE_TYPE',1);
        if($PRIZE_TYPE == 1){  //老代理模式
            // 计算平台收益，一级代理人收益，二级代理人收益
            $serviceCharge = $money * 0.03;
            $oneCharge = $serviceCharge * 0.3;
            $twoCharge = $serviceCharge * 0.2;
            $platformCharge = $serviceCharge - $oneCharge - $twoCharge;
            DB::beginTransaction();
            try {
                // 将一级，二级代理人收益添加到数据库
                if(!empty($user->one_recommend_id)){
                    $one = $this->UserRepository->findByIdUser($user->one_recommend_id);
                    $oneCondition = ["id" => $user->one_recommend_id, "one_commission" => $one->one_commission + $oneCharge, "commission" => $one->commission + $oneCharge];
                    $this->UserRepository->updateAgentMoney($oneCondition);
                    $oneChargeLog = ["betting_user_id" => $user->id, "charge_user_id" => $user->one_recommend_id, "type" => 1, "money" => $oneCharge, "create_time" => time()];
                    $this->UserRepository->addChargeLogs($oneChargeLog);
                }
                if(!empty($user->two_recommend_id)){
                    $two = $this->UserRepository->findByIdUser($user->two_recommend_id);
                    $twoCondition = ["id" => $user->two_recommend_id, "two_commission" => $two->two_commission + $twoCharge, "commission" => $two->commission + $twoCharge];
                    $this->UserRepository->updateAgentMoney($twoCondition);
                    $twoChargeLog = ["betting_user_id" => $user->id, "charge_user_id" => $user->two_recommend_id, "type" => 2, "money" => $twoCharge, "create_time" => time()];
                    $this->UserRepository->addChargeLogs($twoChargeLog);
                }


                // 更改平台收入
                $system = $this->UserRepository->findSystemCharge();
                $this->UserRepository->updateSystemCharge($system->platform_charge + $platformCharge);

                // 将收入记录入库
                $systemChargeLog = ["betting_user_id" => $user->id, "charge_user_id" => 0, "type" => 0, "money" => $platformCharge, "create_time" => time()];
                $this->UserRepository->addChargeLogs($systemChargeLog);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }else{ //新代理模式
            if(!empty($prize_arr)){
                DB::beginTransaction();
                try {
                    foreach($prize_arr as $item)
                    {
                        ##增加用户佣金
                        $condition = ["id" => $item->user_id, "commission" => bcadd($item->commission, $item->prize)];
                        $this->UserRepository->updateAgentMoney($condition);
                        $chargeLog = ["betting_user_id" => $user->id, "charge_user_id" => $item->user_id, "type" => 3, "money" => $item->prize, "create_time" => time()];
                        $this->UserRepository->addChargeLogs($chargeLog);
                    }
                    DB::commit();
                } catch(\Exception $e){
                    DB::rollBack();
                }
            }
        }

    }

    public function Calc_Charge($user, $money)
    {
        $serviceCharge = 0;
        $prize_arr = [];
        $relation = trim($user->invite_relation,'-');
        if($relation){
            $relationArr = explode('-',$relation);
            $cur_rate = $user->rebate_rate;

            foreach ($relationArr as $item) {
                $pUser = $this->UserRepository->findByIdUser($item);
                if ($pUser->rebate_rate > $cur_rate && $pUser->reg_source_id == 0) {
                    $cha_rate = bcsub($pUser->rebate_rate - $cur_rate, 2);
                    $cha_rate = bcmul($cha_rate, 0.01, 3);
                    $prize = bcmul($cha_rate, $money, 2);
                    if ($prize > 0) {
                        $serviceCharge = bcadd($serviceCharge, $prize, 2);
                        $prize_arr[] = [
                            'user_id' => $pUser->id,
                            'prize' => $prize,
                            'commission' => $pUser->commission
                        ];
                    }
                } else {
                    break;
                }
            }
        }
        return compact('serviceCharge','prize_arr');
    }

    //获取用户投注列表
    public function Betting_List($request)
    {
        $limit = $request->input('limit');
        $page = $request->input('page');
        $token = $request->header("token");
        $game_id = $request->input('game_id');
        $data = $request->all();
        $token = urldecode($token);
        $user = explode("+", Crypt::decrypt($token));
        $user_id = $user[0];

        return $this->GameRepository->Betting_List($user_id, $game_id, $limit, ($page - 1) * $limit);
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
    /*结算定时任务
    *获取可结算的期数添加入消费队列
    */
    public function Settlement_Queue_Sd()
    {
        $data = $this->GameRepository->Get_Settlement_Sd();
        if (count($data) > 0) {
            foreach ($data as $val) {
                GameSettlement_Sd::dispatch($val->id, $val->game_id,$val->prize_number)->onQueue('Settlement_Queue_Sd');
            }
            return true;
        }


    }
    //手动开奖
    public function Sd_Prize_Opening($number,$play_id){

        $game_end_time = $this->GameRepository->getEndTime($play_id);
        if (!$game_end_time || $game_end_time < (time() + 10)) {
            return false;
        }
        if(!$this->GameRepository->Game_Is_Queue($play_id)){
            return false;
        }
        if($this->GameRepository->Carried_Sd_Prize($number,$play_id)){

            return true;
        }else{
            return false;
        }
    }

    public function Settlement_Queue_Test()
    {

        $this->Ssc_FourService->ssc(8784);
        //dd($this->GameRepository->Get_New_Sum_Money1());
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
