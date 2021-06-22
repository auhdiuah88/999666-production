<?php


namespace App\Services\Api;


use App\Repositories\Api\IndexRepository;

class IndexService extends BaseService
{

    protected $IndexRepository;

    public function __construct
    (
        IndexRepository $indexRepository
    )
    {
        $this->IndexRepository = $indexRepository;
    }

    public function tips()
    {
        ##获取通知列表
        $this->_data = $this->IndexRepository->tips();
    }

}
