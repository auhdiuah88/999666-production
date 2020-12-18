<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\SettingService;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{

    protected $SettingService;

    public function __construct
    (
        SettingService $settingService
    )
    {
        $this->SettingService = $settingService;
    }

    public function staffId(){
        try{
            $this->SettingService->getStaffId();
            return $this->AppReturn(
                $this->SettingService->_code,
                $this->SettingService->_msg,
                $this->SettingService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr',$e);
            return $this->AppReturn(402,$e->getMessage());
        }
    }

    public function setStaffId(){
        try{
            $validator = Validator::make(request()->input(),
                [
                    'role_id' => "required|gte:1|integer"
                ]
            );
            if($validator->fails())
                return $this->AppReturn(401,$validator->errors()->first());
            $this->SettingService->editStaffId();
            return $this->AppReturn(
                $this->SettingService->_code,
                $this->SettingService->_msg,
                $this->SettingService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr',$e);
            return $this->AppReturn(402,$e->getMessage());
        }
    }

}
