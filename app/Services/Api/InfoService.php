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
        if(!env('CAN_EDIT_BANKCARD',true))
        {
            ##检查银行卡是否已添加
            if($this->InfoRepository->findBankByUserIdFirst($data["user_id"]))
            {
                $this->_code = 402;
                $this->_msg = "Add failed.";
                return;
            }
        }
        ##检查银行卡是否存在
        if($this->InfoRepository->checkBankNum($data['bank_num'])){
            $this->_code = 402;
            $this->_msg = "Bank card already exists";
            return;
        }
        $data["add_time"] = time();
        if ($this->InfoRepository->addBank($data)) {
            $this->_msg = "Added successfully";
        } else {
            $this->_code = 402;
            $this->_msg = "Add failed";
        }
    }

    public function editBank($data, $token)
    {
        if(!env('CAN_EDIT_BANKCARD',true))
        {
            $this->_code = 402;
            $this->_msg = "Edit failed";
            return;
        }
        $data["user_id"] = $this->getUserId($token);
        $data["update_time"] = time();
        if ($this->InfoRepository->editBank($data)) {
            $this->_msg = "Edit successfully";
        } else {
            $this->_code = 402;
            $this->_msg = "Edit failed";
        }
    }

    public function delBank($id, $token)
    {
        if(!env('CAN_EDIT_BANKCARD',true))
        {
            $this->_code = 402;
            $this->_msg = "failed to delete";
            return;
        }
        if ($this->InfoRepository->delBank($id)) {
            $this->_msg = "successfully deleted";
        } else {
            $this->_code = 402;
            $this->_msg = "failed to delete";
        }
    }

    public function updateNickname($nickname, $token)
    {
        $userId = $this->getUserId($token);
        $data = ["nickname" => $nickname, "id" => $userId];
        if ($this->InfoRepository->editUser($data)) {
            $this->_msg = "Edit successfully";
        } else {
            $this->_code = 402;
            $this->_msg = "Edit failed";
        }
    }

    public function updatePassword($data, $token)
    {
        $userId = $this->getUserId($token);
        $userInfo = $this->InfoRepository->findById($userId);
        if ($data["o_password"] != Crypt::decrypt($userInfo->password)) {
            $this->_msg = "The original password is wrong, please re-enter";
            $this->_code = 401;
            return;
        }

        if ($data["f_password"] != $data["l_password"]) {
            $this->_msg = "The two passwords are inconsistent, please re-enter";
            $this->_code = 401;
            return;
        }

        $insertData = ["password" => Crypt::encrypt($data["l_password"]), "id" => $userId];
        if ($this->InfoRepository->editUser($insertData)) {
            $this->_msg = "Edit successfully";
        } else {
            $this->_code = 402;
            $this->_msg = "Edit failed";
        }
    }
}
