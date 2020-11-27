<?php


namespace App\Services\Admin;


use App\Repositories\Admin\SystemRepository;
use App\Services\BaseService;

class SystemService extends BaseService
{
    private $SystemRepository;

    public function __construct(SystemRepository $systemRepository)
    {
        $this->SystemRepository = $systemRepository;
    }

    public function findAll()
    {
        $this->_data = $this->SystemRepository->findAll();
    }

    public function editSystem($data)
    {
        if ($this->SystemRepository->editSystem($data)) {
            $this->_msg = "编辑成功";
        } else {
            $this->_code = 402;
            $this->_msg = "编辑失败";
        }
    }
}
