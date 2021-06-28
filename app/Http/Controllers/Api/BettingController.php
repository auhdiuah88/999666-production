<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\Admin\BettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            $this->bettingService->_data
        );
    }

    public function launch()
    {
        $validator = Validator::make(request()->input(),[
            'game_id' => 'required|integer|gte:1'
        ]);
        if($validator->fails())
        {
            return $this->AppReturn(414,$validator->errors()->first());
        }
        $this->bettingService->launch();
        return $this->AppReturn(
            $this->bettingService->_code,
            $this->bettingService->_msg,
            $this->bettingService->_data
        );
    }
}
