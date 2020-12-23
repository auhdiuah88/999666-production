<?php

namespace App\Services\Admin;


use App\Repositories\Admin\AdminLogRepository;
use App\Services\BaseService;

class AdminLogService extends BaseService
{
    /**
     * @var AdminLogRepository
     */
    private $adminLogRepository;

    public function __construct(AdminLogRepository $adminLogRepository)
    {
        $this->adminLogRepository = $adminLogRepository;
    }

    public function list($page, $limit)
    {
        $this->_data['total'] = $this->adminLogRepository->getCount();
        $this->_data['list'] = $this->adminLogRepository->list(($page - 1) * $limit, $limit);
    }




}
