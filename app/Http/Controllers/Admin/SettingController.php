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
                'one_kill' => ['required', 'gt:0', 'lte:1'],
                'lock_time' => ['required', 'integer', 'gte:10', 'lte:300']
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
                'status' => ['required', Rule::in([1, 2])],
                'start_week' => ['required'],
                'end_week' => ['required'],
                'during_time' => ['required'],
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
                'status' => ['required', Rule::in([1, 2])]
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

    public function h5AlertContent()
    {
        try{
            $this->SettingService->h5AlertContent();
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

    public function setH5AlertContent()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'type' => ['required', 'integer', Rule::in([1, 2])],
                'content' => ['required']
            ]);
            if($validator->fails())
                return $this->AppReturn(
                    403,
                    $validator->errors()->first()
                );
            $this->SettingService->setH5AlertContent();
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
     * 编辑客服信息
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function serviceEdit()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'status' => 'required|integer|in:0,1',
                'btn_1' => ['required'],
                'btn_1.title' => ['required', 'max:15', 'min:1'],
                'btn_1.icon' => ['required'],
                'btn_1.link' => ['required'],
                'btn_2' => ['required'],
                'btn_2.title' => ['required', 'max:15', 'min:1'],
                'btn_2.icon' => ['required'],
                'btn_2.link' => ['required'],
            ]);
            if($validator->fails())
                return $this->AppReturn(
                    403,
                    $validator->errors()->first()
                );
            $this->SettingService->serviceEdit();
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

    public function getService()
    {
        try{
            $this->SettingService->getService();
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

    public function getCrisp()
    {
        try{
            $this->SettingService->getCrisp();
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

    public function crispSave()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'status' => 'required|integer|in:0,1',
                'crisp_website_id' => 'sometimes|required_if:status,1',
            ]);
            if($validator->fails())
                return $this->AppReturn(
                    403,
                    $validator->errors()->first()
                );
            $this->SettingService->crispSave();
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

    public function getApp()
    {
        try{
            $this->SettingService->getApp();
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

    public function appSave()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'status' => 'required|integer|in:0,1',
                'link' => 'required|url',
                'image_id' => 'required|integer|gte:1'
            ]);
            if($validator->fails())
                return $this->AppReturn(
                    403,
                    $validator->errors()->first()
                );
            $this->SettingService->appSave();
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

    public function getAboutUs()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'type' => 'required|integer|in:1,2,3'
            ]);
            if($validator->fails())
                return $this->AppReturn(
                    403,
                    $validator->errors()->first()
                );
            $this->SettingService->getAboutUs();
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

    public function aboutUsSave()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'type' => 'required|integer|in:1,2,3',
                'title' => 'required|between:1,30',
            ]);
            if($validator->fails())
                return $this->AppReturn(
                    403,
                    $validator->errors()->first()
                );
            $this->SettingService->aboutUsSave();
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

    public function getActivity()
    {
        try{
            $this->SettingService->getActivity();
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

    public function activitySave()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'give_away_red_envelopes_status' => 'required|integer|in:0,1'
            ]);
            if($validator->fails())
                return $this->AppReturn(
                    403,
                    $validator->errors()->first()
                );
            $this->SettingService->activitySave();
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

    public function getRechargeRebate()
    {
        try{
            $this->SettingService->getRechargeRebate();
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

    public function rechargeRebateSave()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'status' => 'required|integer|in:0,1',
                'percent' => 'required|numeric|gte:0|lte:1',
                'max_rebate' => 'required|integer|gte:0',
                'min_recharge' => 'required|integer|gte:0',
            ]);
            if($validator->fails())
                return $this->AppReturn(
                    403,
                    $validator->errors()->first()
                );
            $this->SettingService->rechargeRebateSave();
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

    public function registerSave()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'status' => 'required|integer|in:0,1',
                'rebate' => 'required|integer|gte:0',
            ]);
            if($validator->fails())
                return $this->AppReturn(
                    403,
                    $validator->errors()->first()
                );
            $this->SettingService->registerSave();
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

    public function getRegister()
    {
        try{
            $this->SettingService->getRegister();
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

    public function getWithdrawSafe()
    {
        try{
            $this->SettingService->getWithdrawSafe();
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

    public function withdrawSafeSave()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'limit' => 'required|numeric|gte:0',
                'old_password' => 'required|max:30'
            ]);
            if($validator->fails())
                return $this->AppReturn(
                    403,
                    $validator->errors()->first()
                );
            $this->SettingService->withdrawSafeSave();
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

    public function getWithdrawServiceCharge()
    {
        try{
            $this->SettingService->getWithdrawServiceCharge();
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

    public function withdrawServiceChargeSave()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'status' => ['required', Rule::in(0,1)],
                'standard' => ['required', 'gte:0', 'numeric'],
                'charge' => ['required', 'gte:0', 'numeric'],
                'percent' => ['required', 'gte:0', 'lt:1', 'numeric'],
                'free_status' => ['required', Rule::in(0,1)],
                'free_times' => ['required', 'gte:0', 'numeric']
            ]);
            if($validator->fails())
                return $this->AppReturn(
                    403,
                    $validator->errors()->first()
                );
            $this->SettingService->withdrawServiceChargeSave();
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

    public function getIndexAd()
    {
        try{
            $this->SettingService->getIndexAd();
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

    public function indexAdSave()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'status' => ['required', Rule::in(0,1)],
            ]);
            if($validator->fails())
                return $this->AppReturn(
                    403,
                    $validator->errors()->first()
                );
            $this->SettingService->indexAdSave();
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
