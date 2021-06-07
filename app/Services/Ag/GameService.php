<?php


namespace App\Services\Ag;


use App\Repositories\Ag\GameRepository;
use App\Repositories\Ag\UserRepository;
use App\Services\BaseService;

class GameService extends BaseService
{

    protected $GameRepository, $UserRepository;

    public function __construct
    (
        GameRepository $gameRepository,
        UserRepository $userRepository
    )
    {
        $this->GameRepository = $gameRepository;
        $this->UserRepository = $userRepository;
    }

    public function bettingList()
    {
        ##获取彩种
        $game = $this->GameRepository->getGame();
        ##获取列表
        $type = request()->input('type',-1);
        $game_id = request()->input('game_id',0);
        $phone = request()->input('phone','');
        $where = [];
        ##获取用户Id
        $user_ids = $this->UserRepository->getMemberUserIds();
        if($phone)
        {
            ##获取用户
            $user = $this->UserRepository->getByPhone($phone);
            if($user && in_array($user['id'], $user_ids))
            {
                $where['user_id'] = ['=', $user['id']];
            }else{
                $where['user_id'] = ['=', 0];
            }
        }else{
            $where['user_id'] = ['in', $user_ids];
        }

        if($type > -1)
        {
            $where['status'] = ['=', $type];
        }
        if($game_id > 0)
        {
            $where['game_id'] = ['=', $game_id];
            foreach($game as $g)
            {
                if($g['id'] == $game_id)
                {
                    $cur_game = $g['name'];
                    break;
                }
            }
        }else{
            $cur_game = '全部';
        }
        $where['betting_time'] = ['>', strtotime("-7 day")];
//        dd($where);
        $list = $this->GameRepository->bettingList($where);
        $this->_data = compact('game','list','cur_game');
    }

}
