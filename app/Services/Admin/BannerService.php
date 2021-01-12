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
}
