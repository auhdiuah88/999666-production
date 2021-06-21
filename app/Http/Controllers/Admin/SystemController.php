<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\SystemService;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    private $SystemService;

    public function __construct(SystemService $systemService)
    {
        $this->SystemService = $systemService;
    }

    public function findAll()
    {
        $this->SystemService->findAll();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function editSystem(Request $request)
    {
        $this->SystemService->editSystem($request->post());
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function tipsList()
    {
        $this->SystemService->tipsList();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function addTips()
    {
        $this->SystemService->addTips();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function editTips()
    {
        $this->SystemService->editTips();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function delTips()
    {
        $this->SystemService->delTips();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }
}
