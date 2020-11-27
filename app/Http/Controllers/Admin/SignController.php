<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\SignService;
use Illuminate\Http\Request;

class SignController extends Controller
{
    private $SignService;

    public function __construct(SignService $signService)
    {
        $this->SignService = $signService;
    }

    public function findAll(Request $request)
    {
        $this->SignService->findAll($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->SignService->_code,
            $this->SignService->_msg,
            $this->SignService->_data
        );
    }

    public function searchSignLogs(Request $request)
    {
        $this->SignService->searchSignLogs($request->post());
        return $this->AppReturn(
            $this->SignService->_code,
            $this->SignService->_msg,
            $this->SignService->_data
        );
    }
}
