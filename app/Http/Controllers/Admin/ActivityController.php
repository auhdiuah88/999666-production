<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\ActivityService;
use App\Services\Admin\SettingService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ActivityController extends Controller
{

    protected $SettingService, $ActivityService;

    public function __construct
    (
        SettingService $settingService,
        ActivityService $activityService
    )
    {
        $this->SettingService = $settingService;
        $this->ActivityService = $activityService;
    }

    public function inviteFriends()
    {
        try{
            $this->SettingService->getInviteFriends();
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

    public function inviteFriendsSave()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'image_id' => ['required', 'integer', 'gte:1'],
                'status' => ['required', Rule::in(0,1)]
            ]);
            if($validator->fails())
                return $this->AppReturn(
                    403,
                    $validator->errors()->first()
                );
            $this->SettingService->inviteFriendsSave();
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

    public function signProduct()
    {
        try{
            $this->ActivityService->getSignProduct();
            return $this->AppReturn(
                $this->ActivityService->_code,
                $this->ActivityService->_msg,
                $this->ActivityService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr',$e);
            return $this->AppReturn(402,$e->getMessage());
        }
    }

    public function signProductEdit()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'name' => ['required', 'min:2', 'max:50'],
                'status' => ['required', Rule::in(0,1)],
                'amount' => ['numeric', 'gt:0'],
                'daily_rebate' => ['numeric', 'gt:0'],
                'payback_cycle' => ['integer', 'gt:0'],
                'rebate_ratio' => ['numeric'],
                'stock' => ['integer', 'gte:0']
            ]);
            if($validator->fails())
                return $this->AppReturn(403, $validator->errors()->first());
            $this->ActivityService->signProductEdit();
            return $this->AppReturn(
                $this->ActivityService->_code,
                $this->ActivityService->_msg,
                $this->ActivityService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr',$e);
            return $this->AppReturn(402,$e->getMessage());
        }
    }

    public function signSetting()
    {
        try{
            $this->SettingService->signSetting();
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

    public function signSettingSave()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'image_id' => ['required', 'integer', 'gte:1'],
                'status' => ['required', Rule::in(0,1)]
            ]);
            if($validator->fails())
                return $this->AppReturn(
                    403,
                    $validator->errors()->first()
                );
            $this->SettingService->signSettingSave();
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
