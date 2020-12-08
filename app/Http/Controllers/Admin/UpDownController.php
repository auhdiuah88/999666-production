<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\UpDownService;
use Illuminate\Http\Request;

class UpDownController extends Controller
{
    private $UpDownService;

    public function __construct(UpDownService $downService)
    {
        $this->UpDownService = $downService;
    }

    public function findAll(Request $request)
    {
        $this->UpDownService->findAll($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->UpDownService->_code,
            $this->UpDownService->_msg,
            $this->UpDownService->_data
        );
    }

    public function searchUpAndDownLogs(Request $request)
    {
        $this->UpDownService->searchUpAndDownLogs($request->post());
        return $this->AppReturn(
            $this->UpDownService->_code,
            $this->UpDownService->_msg,
            $this->UpDownService->_data
        );
    }
}
