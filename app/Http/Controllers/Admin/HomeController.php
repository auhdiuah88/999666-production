<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    private $HomeService;

    public function __construct(HomeService $homeService)
    {
        $this->HomeService = $homeService;
    }

    public function findAll()
    {
        $this->HomeService->findAll();
        return $this->AppReturn(
            $this->HomeService->_code,
            $this->HomeService->_msg,
            $this->HomeService->_data
        );
    }

    public function searchHome(Request $request)
    {
        $this->HomeService->searchHome($request->post());
        return $this->AppReturn(
            $this->HomeService->_code,
            $this->HomeService->_msg,
            $this->HomeService->_data
        );
    }
}
