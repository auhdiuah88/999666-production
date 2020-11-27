<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\EnvelopeService;
use Illuminate\Http\Request;

class EnvelopeController extends Controller
{
    private $EnvelopeService;

    public function __construct(EnvelopeService $envelopeService)
    {
        $this->EnvelopeService = $envelopeService;
    }

    public function findAll(Request $request)
    {
        $this->EnvelopeService->findAll($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->EnvelopeService->_code,
            $this->EnvelopeService->_msg,
            $this->EnvelopeService->_data
        );
    }

    public function searchEnvelope(Request $request)
    {
        $this->EnvelopeService->searchEnvelope($request->post());
        return $this->AppReturn(
            $this->EnvelopeService->_code,
            $this->EnvelopeService->_msg,
            $this->EnvelopeService->_data
        );
    }
}
