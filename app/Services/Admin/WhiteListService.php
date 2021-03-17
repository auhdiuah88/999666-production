<?php


namespace App\Services\Admin;


use App\Repositories\Admin\WhiteListRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Redis;

class WhiteListService extends BaseService
{
    private $WhiteListRepository;

    public function __construct(WhiteListRepository $listRepository)
    {
        $this->WhiteListRepository = $listRepository;
    }

    public function findAll($page, $limit)
    {
        $list = $this->WhiteListRepository->findAll(($page - 1) * $limit, $limit);
        $total = $this->WhiteListRepository->countAll();
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function findById($id)
    {
        $this->_data = $this->WhiteListRepository->findById($id);
    }

    public function addIp($data)
    {
        $data["create_time"] = time();
        if ($this->WhiteListRepository->addIp($data)) {
            $this->updateCacheIps();
            $this->_msg = "添加成功";
        } else {
            $this->_code = 402;
            $this->_msg = "添加失败";
        }
    }

    public function editIp($data)
    {
        if ($this->WhiteListRepository->editIp($data)) {
            $this->updateCacheIps();
            $this->_msg = "编辑成功";
        } else {
            $this->_code = 402;
            $this->_msg = "编辑失败";
        }
    }

    public function delIp($id)
    {
        if ($this->WhiteListRepository->delIp($id)) {
            $this->updateCacheIps();
            $this->_msg = "删除成功";
        } else {
            $this->_code = 402;
            $this->_msg = "删除失败";
        }
    }

    public function updateCacheIps()
    {
        Redis::del('WHITE_IPS');
        $ips = $this->WhiteListRepository->ips();
        foreach($ips as $ip)
        {
            Redis::sadd('WHITE_IPS', $ip);
        }
    }
}
