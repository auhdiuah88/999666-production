<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\SettingService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

    /**
     * 获取员工角色ID
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
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

    /**
     * 设置员工角色ID
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function setStaffId(){
        try{
            $validator = Validator::make(request()->input(),
                [
                    'role_id' => "required|gte:1|integer"
                ]
            );
            if($validator->fails())
                return $this->AppReturn(402,$validator->errors()->first());
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

    /**
     * 游戏开奖规则
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function gameRule()
    {
        try{
            $this->SettingService->gameRule();
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

    /**
     * 设置游戏开奖规则
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function setGameRule()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'id' => ['required', 'integer', 'gt:0'],
                'open_type' => ['required', Rule::in([1, 2, 3])],
                'date_kill' => ['required', 'gt:0', 'lte:1'],
                'one_kill' => ['required', 'gt:0', 'lte:1']
            ]);
            if($validator->fails())
                return $this->AppReturn(
                    403,
                    $validator->errors()->first()
                );
            $this->SettingService->setGameRule();
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

    /**
     * 获取提现配置
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function withdrawConfig()
    {
        try{
            $this->SettingService->withdrawConfig();
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

    public function setWithdrawConfig()
    {
        try {
            $validator = Validator::make(request()->input(), [
                'type' => ['required'],
                'max' => ['required', 'integer', 'gt:1'],
                'min' => ['required', 'integer', 'gt:1'],
                'btn' => ['required'],
                'secret_key' => ['required'],
                'merchant_id' => ['required'],
                'status' => ['required', Rule::in([0, 1])]
            ]);
            if ($validator->fails())
                return $this->AppReturn(403, $validator->errors()->first());
            $this->SettingService->setWithdrawConfig();
            return $this->AppReturn(
                $this->SettingService->_code,
                $this->SettingService->_msg,
                $this->SettingService->_data
            );
        } catch (\Exception $e) {
            $this->logError('adminerr', $e);
            return $this->AppReturn(402, $e->getMessage());
        }
    }

    /**
     * 获取充值配置
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function rechargeConfig()
    {
        try{
            $this->SettingService->rechargeConfig();
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

    public function setRechargeConfig()
    {
        try {
            $validator = Validator::make(request()->input(), [
                'type' => ['required'],
                'max' => ['required', 'integer', 'gte:1'],
                'min' => ['required', 'integer', 'gte:1'],
                'btn' => ['required'],
                'secret_key' => ['required'],
                'merchant_id' => ['required'],
                'status' => ['required', Rule::in([0, 1])]
            ]);
            if ($validator->fails())
                return $this->AppReturn(403, $validator->errors()->first());
            $this->SettingService->setRechargeConfig();
            return $this->AppReturn(
                $this->SettingService->_code,
                $this->SettingService->_msg,
                $this->SettingService->_data
            );
        } catch (\Exception $e) {
            $this->logError('adminerr', $e);
            return $this->AppReturn(402, $e->getMessage());
        }
    }

    public function getGroupLeaderRoleId()
    {
        $this->SettingService->queryGroupLeaderRoleId();
        return $this->AppReturn(
            $this->SettingService->_code,
            $this->SettingService->_msg,
            $this->SettingService->_data
        );
    }

    public function saveGroupLeaderRoleId()
    {
        $validator = Validator::make(request()->post(),
            [
                'role_id' => "required|gte:1|integer"
            ]
        );
        if($validator->fails())
            return $this->AppReturn(402,$validator->errors()->first());
        $this->SettingService->saveGroupLeaderRoleId();
        return $this->AppReturn(
            $this->SettingService->_code,
            $this->SettingService->_msg,
            $this->SettingService->_data
        );
    }

}
