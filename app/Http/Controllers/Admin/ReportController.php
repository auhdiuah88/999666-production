<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private $ReportService;

    public function __construct(ReportService $reportService)
    {
        $this->ReportService = $reportService;
    }

    public function findAll(Request $request)
    {
        $this->ReportService->findAll($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->ReportService->_code,
            $this->ReportService->_msg,
            $this->ReportService->_data
        );
    }

    public function searchReport(Request $request)
    {
        $this->ReportService->searchReport($request->post());
        return $this->AppReturn(
            $this->ReportService->_code,
            $this->ReportService->_msg,
            $this->ReportService->_data
        );
    }
}
