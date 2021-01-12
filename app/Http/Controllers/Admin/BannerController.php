<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\BannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function index(){}

    public function add(Request $request)
    {
        $validator = Validator::make($request->post(),[
            'uploads_id' => 'required|int',
            'location' => 'required',
            'type' => 'required',
        ]);
        if($validator->fails()){
            return $this->AppReturn(402, $validator->errors()->first());
        }
        $this->bannerService->add($request->only(['uploads_id', 'location', 'type', 'url']));
        return $this->AppReturn(
            $this->bannerService->_code,
            $this->bannerService->_msg,
            $this->bannerService->_data
        );
    }
}
