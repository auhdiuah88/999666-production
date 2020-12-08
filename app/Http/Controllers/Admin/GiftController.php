<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\GiftService;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    private $GiftService;

    public function __construct(GiftService $giftService)
    {
        $this->GiftService = $giftService;
    }

    public function findAll(Request $request)
    {
        $this->GiftService->findAll($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->GiftService->_code,
            $this->GiftService->_msg,
            $this->GiftService->_data
        );
    }

    public function searchGiftLogs(Request $request)
    {
        $this->GiftService->searchGiftLogs($request->post());
        return $this->AppReturn(
            $this->GiftService->_code,
            $this->GiftService->_msg,
            $this->GiftService->_data
        );
    }
}
