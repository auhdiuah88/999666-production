<?php


namespace App\Services\Admin;


use App\Repositories\Admin\EnvelopeRepository;
use App\Services\BaseService;

class EnvelopeService extends BaseService
{
    private $EnvelopeRepository;

    public function __construct(EnvelopeRepository $envelopeRepository)
    {
        $this->EnvelopeRepository = $envelopeRepository;
    }

    public function findAll($page, $limit)
    {
        $list = $this->EnvelopeRepository->findAll(($page - 1) * $limit, $limit);
        $total = $this->EnvelopeRepository->countAll();
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function searchEnvelope($data)
    {
        $page = $data["page"];
        $limit = $data["limit"];
        $offset = ($page - 1) * $limit;
        if (array_key_exists("phone", $data["conditions"])) {
            $ids = $this->EnvelopeRepository->getUserIds($data["conditions"]["phone"]);
            unset($data["conditions"]["phone"]);
            unset($data["ops"]["phone"]);
            $data["conditions"]["user_id"] = $ids;
            $data["ops"]["user_id"] = "in";
        }
        $list = $this->EnvelopeRepository->searchEnvelope($data, $offset, $limit);
        $total = $this->EnvelopeRepository->countSearchEnvelope($data);
        $this->_data = ["total" => $total, "list" => $list];
    }
}
