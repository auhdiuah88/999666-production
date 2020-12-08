<?php


namespace App\Services\Admin;


use App\Repositories\Admin\GiftRepository;
use App\Services\BaseService;

class GiftService extends BaseService
{
    private $GiftRepository;

    public function __construct(GiftRepository $giftRepository)
    {
        $this->GiftRepository = $giftRepository;
    }

    public function findAll($page, $limit)
    {
        $list = $this->GiftRepository->findAll(($page - 1) * $limit, $limit);
        $total = $this->GiftRepository->countAll();
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function searchGiftLogs($data)
    {
        $data = $this->getUserIds($data, "user_id");
        $offset = ($data["page"] - 1) * $data["limit"];
        $limit = $data["limit"];
        $list = $this->GiftRepository->searchGiftLogs($data, $offset, $limit);
        $total = $this->GiftRepository->countSearchGiftLogs($data);
        $this->_data = ["total" => $total, "list" => $list];
    }
}
