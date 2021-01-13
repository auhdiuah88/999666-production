<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cx_Banner;
use App\Services\Api\BannerService;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * @var BannerService
     */
    private $bannerService;

    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    public function banners(Request $request)
    {
        return $this->AppReturn(200, 'successfully', $this->bannerService->bannersByLocation($request->get('location', Cx_Banner::LOCATION_LIST[1]['id'])));
    }
}
