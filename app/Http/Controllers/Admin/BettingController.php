<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\BettingService;
use Illuminate\Http\Request;

class BettingController extends Controller
{
    private $BettingService;

    public function __construct(BettingService $bettingService)
    {
        $this->BettingService = $bettingService;
    }

    public function findAll(Request $request)
    {
        $this->BettingService->findAll($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->BettingService->_code,
            $this->BettingService->_msg,
            $this->BettingService->_data
        );
    }

    public function searchBettingLogs(Request $request)
    {
        $this->BettingService->searchBettingLogs($request->post());
        return $this->AppReturn(
            $this->BettingService->_code,
            $this->BettingService->_msg,
            $this->BettingService->_data
        );
    }

    public function statisticsBettingLogs()
    {
        $this->BettingService->statisticsBettingLogs();
        return $this->AppReturn(
            $this->BettingService->_code,
            $this->BettingService->_msg,
            $this->BettingService->_data
        );
    }
}
