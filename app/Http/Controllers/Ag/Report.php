<?php


namespace App\Http\Controllers\Ag;


use App\Services\Ag\ReportsService;

class Report extends Base
{

    protected $ReportService;

    public function __construct
    (
        ReportsService $reportsService
    )
    {
        $this->ReportService = $reportsService;
    }

    public function index()
    {
        $this->ReportService->getAgReport();
        return view('ag.report', ['idx'=>2, 'data'=>$this->ReportService->_data]);
    }

    public function mIndex()
    {
        $this->ReportService->getAgReport();
        return view('ag.m.report', ['title'=>trans('ag.agent_report'), 'data'=>$this->ReportService->_data, 'prev'=>1]);
    }

}
