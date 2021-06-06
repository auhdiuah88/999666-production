<?php


namespace App\Services\Ag;


use App\Repositories\Ag\LoginRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Crypt;

class LoginService extends BaseService
{

    protected $LoginRepository;

    public function __construct
    (
        LoginRepository $loginRepository
    )
    {
        $this->LoginRepository = $loginRepository;
    }

    public function login($data): bool
    {
        ##判断登录次数
        if($this->LoginRepository->getLoginTimes() > 5)
        {
            $this->_code = 403;
            $this->_msg = '登录次数频繁,请20分钟之后再试';
            return false;
        }
        ##获取用户信息
        $user = $this->LoginRepository->getUserByPhone($data['phone']);
        if(empty($user))
        {
            $this->_msg = '账号或密码错误';
            $this->_code = 403;
            return false;
        }
        ##判断密码
        if (Crypt::decrypt($user->password) != $data['pwd']) {
            $this->LoginRepository->setLoginTimes();
            $this->_code = 403;
            $this->_msg = '账号或密码错误';
            return false;
        }
        ##判断用户类型
        if($user->user_type != 1)
        {
            $this->LoginRepository->setLoginTimes();
            $this->_code = 403;
            $this->_msg = '非代理不可登录';
            return false;
        }
        ##判断用户状态
        if($user->status != 0)
        {
            $this->LoginRepository->setLoginTimes();
            $this->_code = 403;
            $this->_msg = '账号已冻结';
            return false;
        }
        ##登录成功
        $this->LoginRepository->doLogin($user);
        return true;
    }

    public function logout()
    {
        $this->LoginRepository->doLogout();
    }

}
