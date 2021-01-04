<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\Admin\BettingService;
use Illuminate\Http\Request;

class BettingController extends Controller
{
    /**
     * @var BettingService
     */
    private $bettingService;

    public function __construct(BettingService $bettingService)
    {
        $this->bettingService = $bettingService;
    }

    public function statistics(Request $request)
    {
        $this->bettingService->statistics($request->get('type', 1));
        return $this->AppReturn(
            $this->bettingService->_code,
            $this->bettingService->_msg,
            $this->bettingService->_data,
        );
    }
}
