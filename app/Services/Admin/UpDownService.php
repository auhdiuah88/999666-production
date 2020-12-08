<?php


namespace App\Services\Admin;


use App\Repositories\Admin\UpDownRepository;
use App\Services\BaseService;

class UpDownService extends BaseService
{
    private $UpDownRepository;

    public function __construct(UpDownRepository $downRepository)
    {
        $this->UpDownRepository = $downRepository;
    }

    public function findAll($page, $limit)
    {
        $total = $this->UpDownRepository->countAll();
        $list = $this->UpDownRepository->findAll(($page - 1) * $limit, $limit);
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function searchUpAndDownLogs($data)
    {
        dump($data);
        $data = $this->getUserIds($data, "user_id");
        $offset = ($data["page"] - 1) * $data["limit"];
        $limit = $data["limit"];
        dd($data);
        $list = $this->UpDownRepository->searchUpAndDownLogs($data, $offset, $limit);
        $total = $this->UpDownRepository->countSearchUpAndDownLogs($data);
        $this->_data = ["total" => $total, "list" => $list];
    }
}
