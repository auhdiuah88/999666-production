<?php


namespace App\Services\Api;


use App\Repositories\Api\AddressRepository;
use App\Services\BaseService;

class AddressService extends BaseService
{
    private $AddressRepository;

    public function __construct(AddressRepository $addressRepository)
    {
        $this->AddressRepository = $addressRepository;
    }

    public function findAll($token)
    {
        $userId = $this->getUserId($token);
        $this->_data = $this->AddressRepository->findAll($userId);
    }

    public function findById($id)
    {
        $this->_data = $this->AddressRepository->findById($id);
    }

    public function addAddress($data, $token)
    {
        $data["user_id"] = $this->getUserId($token);
        if ($this->AddressRepository->addAddress($data)) {
            $this->_msg = "添加成功";
        } else {
            $this->_code = 402;
            $this->_msg = "添加失败";
        }
    }

    public function editAddress($data, $token)
    {
        $data["user_id"] = $this->getUserId($token);
        if ($this->AddressRepository->editAddress($data)) {
            $this->_msg = "编辑成功";
        } else {
            $this->_code = 402;
            $this->_msg = "编辑失败";
        }
    }

    public function delAddress($id)
    {
        if ($this->AddressRepository->delAddress($id)) {
            $this->_msg = "删除成功";
        } else {
            $this->_code = 402;
            $this->_msg = "删除失败";
        }
    }
}
