<?php

namespace App\Services\Api;



use App\Repositories\Api\BannerRepository;

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

    public function bannersByLocation($location)
    {
        return $this->bannerRepository->bannersByLocation($location);
    }
}
