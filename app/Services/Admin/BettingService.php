<?php


namespace App\Services\Admin;


use App\Libs\Games\GameContext;
use App\Libs\Games\WDYY\Client;
use App\Libs\Games\V8\V8log;
use App\Repositories\Admin\BettingRepository;
use App\Repositories\Admin\UserRepository;
use App\Services\BaseService;

class BettingService extends BaseService
{
    private $BettingRepository, $UserRepository, $GameContext;

    public function __construct
    (
        BettingRepository $bettingRepository,
        UserRepository $userRepository,
        GameContext $gameContext
    )
    {
        $this->BettingRepository = $bettingRepository;
        $this->UserRepository = $userRepository;
        $this->GameContext = $gameContext;
    }

    public function findAll($page, $limit, $sort=[])
    {
//        print_r($sort);die;
        $list = $this->BettingRepository->findAll(($page - 1) * $limit, $limit, $sort);
        $total = $this->BettingRepository->countAll([]);
        $this->_data = ["total" => $total, "list" => $list];
    }

    /**
     * 获取最新的数据
     */
    public function getNewest()
    {
        return $this->BettingRepository->getNewest();
    }

    public function searchBettingLogs($data)
    {
        $page = $data["page"];
        $limit = $data["limit"];
        $sort_field = $data['sort_field'];
        $sort_sort = $data['sort_sort'];
        $offset = ($page - 1) * $limit;
        $data = $this->assemblyParameters($data);
        $list = $this->BettingRepository->searchBettingLogs($data, $offset, $limit, $sort_field, $sort_sort);
        $total = $this->BettingRepository->countSearchBettingLogs($data);
        $this->_data = ["total" => $total, "list" => $list];

    }

    public function statisticsBettingLogs($data)
    {
        $data = $this->assemblyParameters($data);
        $this->_data["betting_count"] = $this->BettingRepository->countAll($data);
        $this->_data["betting_money"] = $this->BettingRepository->sumAll($data,"money");
        $this->_data["service_charge"] = $this->BettingRepository->sumAll($data,"service_charge");
        $this->_data["win_money"] = $this->BettingRepository->sumAll($data,"win_money");
        $this->_data["betting_user"] = $this->BettingRepository->countByUser($data);
    }

    public function assemblyParameters($data)
    {
        if (!array_key_exists("conditions", $data)) {
            return $data;
        }
        if (array_key_exists("selection", $data["conditions"])) {
            $data["conditions"]["game_c_x_id"] = $this->BettingRepository->findPlayIds($data["conditions"]["selection"]);
            $data["ops"]["game_c_x_id"] = "in";
            unset($data["conditions"]["selection"]);
            unset($data["ops"]["selection"]);
        }

        if (array_key_exists("number", $data["conditions"])) {
            $data["conditions"]["game_p_id"] = $this->BettingRepository->findNumberId($data["conditions"]["number"]);
            $data["ops"]["game_p_id"] = "in";
            unset($data["conditions"]["number"]);
            unset($data["ops"]["number"]);
        }

        if (array_key_exists("phone", $data["conditions"])) {
            $data["conditions"]["user_id"] = $this->BettingRepository->findUserId($data["conditions"]["phone"]);
            $data["ops"]["user_id"] = "=";
            unset($data["conditions"]["phone"]);
            unset($data["ops"]["phone"]);
        }

        if (array_key_exists("reg_source_id", $data["conditions"])) {
            if($data["conditions"]["reg_source_id"] != '')
            {
                $data["conditions"]["user_id"] = $this->UserRepository->getSourceUserIds($data["conditions"]["reg_source_id"]);
                $data["ops"]["user_id"] = "in";
            }
            unset($data["conditions"]["reg_source_id"]);
            unset($data["ops"]["reg_source_id"]);
        }
        return $data;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function statistics($type)
    {
        $this->_data = $this->BettingRepository->statistics($type);
    }

    /**
     * 下注提醒用户列表
     */
    public function noticeList()
    {
        $size = $this->sizeInput();
        $user_id = $this->intInput('user_id');
        $phone = $this->strInput('phone');
        $where =
            [
                'is_betting_notice' => ['=', 1]
            ];
        if($user_id)
            $where['id'] = ['=', $user_id];
        if($phone)
            $where['phone'] = ['=', $phone];
        $this->_data = $this->BettingRepository->noticeList($where, $size);
    }

    public function noticeBettingList()
    {
        $size = $this->sizeInput();
        $sort = $this->intInput('sort');
        $user_id = $this->intInput('user_id');
        $type = $this->intInput('type');

        $where =
            [
                'user_id' => ['=', $user_id]
            ];
        if($type)
            $where['game_id'] = ['=', $type];
        $this->_data = $this->BettingRepository->noticeBettingList($where, $sort, $size);
//        $this->_data = $this->BettingRepository->noticeBettingList($sort, $size);
    }

    public function launch()
    {
        try{
            $game_id = $this->intInput('game_id');
            ##获取游戏信息
            $game = $this->BettingRepository->gameInfo($game_id);
            if(!$game)
            {
                throw new \Exception("The game doesn't exist");
            }
            if($game->status != 1)
            {
               throw new \Exception('The game has been taken off the shelves');
            }
            if(!$game->cate)
            {
                throw new \Exception('Abnormal game data');
            }
            if($game->cate->status != 1)
            {
                throw new \Exception('The game has been taken off the shelves .');
            }
            if($game->other == '')
            {
                throw new \Exception('The game has been taken off the shelves ..');
            }
            ##
            if($game["link"] == "v8"){
                $Client = $this->GameContext->getStrategy('v8');
            }else if($game["link"] == "wdyy"){
                $Client = $this->GameContext->getStrategy('wdyy');
            }else if($game["link"] == "icg"){
                $Client = $this->GameContext->getStrategy('icg');
            }
            if(!$Client->launch($game->other))
            {
                $this->_msg = $Client->_msg;
                $this->_code = 415;
                $this->_data = $Client;
                return;
            }
            $this->_data = $Client->_data;
            return;
        }catch(\Exception $e){
            $this->_msg = $e->getMessage();
            $this->_code = 414;
            return;
        }
    }

}
