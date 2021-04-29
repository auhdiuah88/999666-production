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

    public function findContext()
    {
        try{
            $this->HomeService->findAllContext();
            return $this->AppReturn(
                $this->HomeService->_code,
                $this->HomeService->_msg,
                $this->HomeService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function searchContext(Request $request)
    {
        try{
            $this->HomeService->searchAllContext($request->post());
            return $this->AppReturn(
                $this->HomeService->_code,
                $this->HomeService->_msg,
                $this->HomeService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function searchContext2(Request $request)
    {
        try{
            $this->HomeService->searchAllContext2($request->post());
            return $this->AppReturn(
                $this->HomeService->_code,
                $this->HomeService->_msg,
                $this->HomeService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
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

    public function getSystemTime(){
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $this->AppReturn(
            200,
            '',
            $msectime
        );
    }
}
