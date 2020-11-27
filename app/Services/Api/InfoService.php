<?php


namespace App\Services\Api;


use App\Repositories\Api\InfoRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Crypt;

class InfoService extends BaseService
{
    private $InfoRepository;

    public function __construct(InfoRepository $infoRepository)
    {
        $this->InfoRepository = $infoRepository;
    }

    public function getInfo($token)
    {
        $user_id = $this->getUserId($token);
        $this->_data = $this->InfoRepository->findById($user_id);
    }

    public function getBanks($token)
    {
        $user_id = $this->getUserId($token);
        $this->_data = $this->InfoRepository->findBankById($user_id);
    }

    public function getBankById($id)
    {
        $this->_data = $this->InfoRepository->findBankFirst($id);
    }

    public function addBank($data, $token)
    {
        $data["user_id"] = $this->getUserId($token);
        $data["add_time"] = time();
        if ($this->InfoRepository->addBank($data)) {
            $this->_msg = "添加成功";
        } else {
            $this->_code = 402;
            $this->_msg = "添加失败";
        }
    }

    public function editBank($data, $token)
    {
        $data["user_id"] = $this->getUserId($token);
        $data["update_time"] = time();
        if ($this->InfoRepository->editBank($data)) {
            $this->_msg = "编辑成功";
        } else {
            $this->_code = 402;
            $this->_msg = "编辑失败";
        }
    }

    public function delBank($id, $token)
    {
        if ($this->InfoRepository->delBank($id)) {
            $this->_msg = "删除成功";
        } else {
            $this->_code = 402;
            $this->_msg = "删除失败";
        }
    }

    public function updateNickname($nickname, $token)
    {
        $userId = $this->getUserId($token);
        $data = ["nickname" => $nickname, "id" => $userId];
        if ($this->InfoRepository->editUser($data)) {
            $this->_msg = "编辑成功";
        } else {
            $this->_code = 402;
            $this->_msg = "编辑失败";
        }
    }

    public function updatePassword($data, $token)
    {
        $userId = $this->getUserId($token);
        $userInfo = $this->InfoRepository->findById($userId);
        if ($data["o_password"] != Crypt::decrypt($userInfo->password)) {
            $this->_msg = "原密码错误，请重新输入";
            $this->_code = 401;
            return;
        }

        if ($data["f_password"] != $data["l_password"]) {
            $this->_msg = "两次输入密码不一致，请重新输入";
            $this->_code = 401;
            return;
        }

        $insertData = ["password" => Crypt::encrypt($data["l_password"]), "id" => $userId];
        if ($this->InfoRepository->editUser($insertData)) {
            $this->_msg = "编辑成功";
        } else {
            $this->_code = 402;
            $this->_msg = "编辑失败";
        }
    }
}
