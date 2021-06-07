<?php


namespace App\Services\Ag;


use App\Repositories\Ag\UserRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Cache;

class UserService extends BaseService
{

    protected $UserRepository;

    public function __construct
    (
        UserRepository $userRepository
    )
    {
        $this->UserRepository = $userRepository;
    }

    public function addLink($data): bool
    {
        $user = Cache::get('user');
        $user_id = $user['id'];
        ##获取用户
        $userInfo = $this->UserRepository->getById($user_id);
        if($userInfo['rebate_rate'] < $data['rate'])
        {
            $this->_msg = '下级的返点不能高于自身返点';
            $this->_code = 403;
            return false;
        }
        $link = getInviteLink($user['code'],floatval($data['rate']),intval($data['user_type']));
        if(!$link)
        {
            $this->_msg = '生成邀请链接失败';
            $this->_code = 403;
            return false;
        }
        $insert = [
            'rebate_percent' => floatval($data['rate']),
            'type' => intval($data['user_type']),
            'user_id' => $user_id,
            'link' => $link
        ];
        $res = $this->UserRepository->addLink($insert);
        if(!$res['id'])
        {
            $this->_msg = '保存邀请链接失败';
            $this->_code = 403;
            return false;
        }
        return true;
    }

    public function getLinkList()
    {
        $this->_data = $this->UserRepository->getLinkList();
    }

    public function delLink($data): bool
    {
        $res = $this->UserRepository->delLink($data['id']);
        if(!$res)
        {
            $this->_msg = trans('ag.del_link_fail');
            $this->_code = 403;
            return false;
        }
        return true;
    }

    public function getUserList()
    {
        $phone = request()->input('phone','');
        $user_type = request()->input('user_type',0);
        $this->_data = $this->UserRepository->getUserList($phone, $user_type);
    }

}
