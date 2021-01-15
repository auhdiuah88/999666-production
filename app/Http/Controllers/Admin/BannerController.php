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

    public function index(Request $request)
    {
        $this->bannerService->index($request->get("page", 1), $request->get("limit", 10));
        return $this->AppReturn(
            $this->bannerService->_code,
            $this->bannerService->_msg,
            $this->bannerService->_data
        );
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->post(),[
            'uploads_id' => 'required|int',
            'location' => 'required',
            'type' => 'required',
            'sort' => 'required|int',
        ]);
        if($validator->fails()){
            return $this->AppReturn(402, $validator->errors()->first());
        }
        $this->bannerService->add($request->only(['uploads_id', 'location', 'type', 'url', 'sort']));
        return $this->AppReturn(
            $this->bannerService->_code,
            $this->bannerService->_msg,
            $this->bannerService->_data
        );
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->post(),[
            'id' => 'required|int',
            'sort' => 'required|int',
        ]);
        if($validator->fails()){
            return $this->AppReturn(402, $validator->errors()->first());
        }
        $this->bannerService->save($request->only(['uploads_id', 'location', 'type', 'url', 'id', 'sort']));
        return $this->AppReturn(
            $this->bannerService->_code,
            $this->bannerService->_msg,
            $this->bannerService->_data
        );
    }

    public function del(Request $request)
    {
        $validator = Validator::make($request->post(),[
            'id' => 'required|int',
        ]);
        if($validator->fails()){
            return $this->AppReturn(402, $validator->errors()->first());
        }
        $this->bannerService->del($request->get('id'));
        return $this->AppReturn(
            $this->bannerService->_code,
            $this->bannerService->_msg,
            $this->bannerService->_data
        );
    }
}
