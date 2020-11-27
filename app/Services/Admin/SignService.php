<?php


namespace App\Services\Admin;


use App\Repositories\Admin\SignRepository;
use App\Services\BaseService;

class SignService extends BaseService
{
    private $SignRepository;

    public function __construct(SignRepository $signRepository)
    {
        $this->SignRepository = $signRepository;
    }

    public function findAll($page, $limit)
    {
        $list = $this->SignRepository->findAll(($page - 1) * $limit, $limit);
        $total = $this->SignRepository->countAll();
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function searchSignLogs($data)
    {
        $page = $data["page"];
        $limit = $data["limit"];
        $offset = ($page - 1) * $limit;
        $list = $this->SignRepository->searchSignLogs($data, $offset, $limit);
        $total = $this->SignRepository->countSearchSignLogs($data);
        $this->_data = ["total" => $total, "list" => $list];
    }
}
