<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Services\Api\AddressService;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    private $AddressService;

    public function __construct(AddressService $service)
    {
        $this->AddressService = $service;
    }


    public function findAll(Request $request)
    {
        $this->AddressService->findAll($request->header("token"));
        return $this->AppReturn(
            $this->AddressService->_code,
            $this->AddressService->_msg,
            $this->AddressService->_data
        );
    }

    public function findById(Request $request)
    {
        $this->AddressService->findById($request->get("id"));
        return $this->AppReturn(
            $this->AddressService->_code,
            $this->AddressService->_msg,
            $this->AddressService->_data
        );
    }

    public function addAddress(Request $request)
    {
        $this->AddressService->addAddress($request->post(), $request->header("token"));
        return $this->AppReturn(
            $this->AddressService->_code,
            $this->AddressService->_msg,
            $this->AddressService->_data
        );
    }

    public function editAddress(Request $request)
    {
        $this->AddressService->editAddress($request->post(), $request->header("token"));
        return $this->AppReturn(
            $this->AddressService->_code,
            $this->AddressService->_msg,
            $this->AddressService->_data
        );
    }

    public function delAddress(Request $request)
    {
        $this->AddressService->delAddress($request->post("id"));
        return $this->AppReturn(
            $this->AddressService->_code,
            $this->AddressService->_msg,
            $this->AddressService->_data
        );
    }
}
