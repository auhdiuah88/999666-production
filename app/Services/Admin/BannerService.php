<?php
/**
 * Created by PhpStorm.
 * User: ck
 * Date: 2021-01-12
 * Time: 16:27
 */

namespace App\Services\Admin;



use App\Repositories\Admin\BannerRepository;
use App\Services\BaseService;

class BannerService extends BaseService
{
    /**
     * @var BannerRepository
     */
    private $bannerRepository;

    public function __construct(BannerRepository $bannerRepository)
    {
        $this->bannerRepository = $bannerRepository;
    }

    public function add($insertData)
    {
        return $this->bannerRepository->add($insertData);
    }

    public function index($page, $limit)
    {
        $this->_data['total'] = $this->bannerRepository->count();
        if ($list = $this->bannerRepository->index(($page - 1) * $limit, $limit)) {
            $this->_data['list'] = $list->toArray();
        } else {
            $this->_data['list'] = [];
        }
    }

    public function del($id)
    {
        $res = $this->bannerRepository->del($id);
        if($res === false){
            $this->_code = 402;
            $this->_msg = "操作失败";
            return false;
        }
    }

    public function save($post)
    {
        $res = $this->bannerRepository->save($post);
        if($res === false){
            $this->_code = 402;
            $this->_msg = "操作失败";
            return false;
        }
    }
}
