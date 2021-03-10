<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\SettingService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ActivityController extends Controller
{

    protected $SettingService;

    public function __construct
    (
        SettingService $settingService
    )
    {
        $this->SettingService = $settingService;
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

}
