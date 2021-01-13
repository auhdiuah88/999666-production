<?php

namespace App\Repositories\Api;


use App\Models\Cx_Banner;

class BannerRepository
{
    /**
     * @var Cx_Banner
     */
    private $cx_Banner;

    public function __construct(Cx_Banner $cx_Banner)
    {
        $this->cx_Banner = $cx_Banner;
    }

    public function bannersByLocation($location)
    {
        return $this->cx_Banner
            ->with([
                'uploads' => function ($query){
                    $query->select(["image_id", "path"]);
                }
            ])
            ->select(['id', 'uploads_id', 'url', 'type', 'location'])
            ->where('location', $location)
            ->get()
            ->toArray();
    }
}
