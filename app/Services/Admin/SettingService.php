<?php


namespace App\Services\Admin;


use App\Dictionary\GameDic;
use App\Dictionary\SettingDic;
use App\Repositories\Admin\SettingRepository;
use App\Repositories\Admin\UploadsRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SettingService extends BaseService
{

    private $SettingRepository, $UploadsRepository;

    public function __construct
    (
        SettingRepository $settingRepository,
        UploadsRepository $uploadsRepository
    )
    {
        $this->SettingRepository = $settingRepository;
        $this->UploadsRepository = $uploadsRepository;
    }

    /**
     * get staff role id
     * @return mixed
     */
    public function getStaffId(){
        $staff = $this->SettingRepository->getStaff();
        $this->_data = $staff ? ["role_id"=>$staff->setting_value['role_id']]: ["role_id"=>""] ;
        return true;
    }

    /**
     * edit staff role id
     * @return bool
     */
    public function editStaffId(){
        $role_id = $this->intInput('role_id');
        ##判断角色是否存在
        if(!$this->SettingRepository->checkRole($role_id)){
            $this->_code = 401;
            $this->_msg = "角色不存在";
            return false;
        }
        $role = $this->SettingRepository->getStaff();
        if($role){
            $res = $this->SettingRepository->editStaff($role_id);
        }else{
            $res = $this->SettingRepository->addStaff($role_id);
        }
        if($res === false){
            $this->_code = 401;
            $this->_msg = "操作失败";
            return false;
        }
        $this->_msg = "操作成功";
        return true;
    }

    public function gameRule()
    {
        $games = $this->SettingRepository->gameRule();
        $rules = array_values(GameDic::getOpenType());
        $this->_data = compact('games','rules');
    }

    public function setGameRule()
    {
        $id = $this->intInput('id');
        $open_type = $this->intInput('open_type');
        $date_kill = $this->floatInput('date_kill');
        $one_kill = $this->floatInput('one_kill');
        $lock_time = $this->intInput('lock_time');
        $data = compact('open_type','date_kill','one_kill','lock_time');
        $res = $this->SettingRepository->setGameRule($id, $data);
        if($res === false){
            $this->_code = 403;
            $this->_msg = '操作失败';
            return false;
        }
        $this->_msg = '操作成功';
        return true;
    }

    public function withdrawConfig()
    {
        $withdraw_type = config('pay.withdraw',[]);
        $setting_value = $this->SettingRepository->getWithdraw();
        $config = [];
        foreach($withdraw_type as $key => $item){
            $config[] = [
                'type' => $key,
                'limit' => isset($setting_value[$key])?$setting_value[$key]['limit']:['max'=>0,'min'=>0],
                'btn' => isset($setting_value[$key])?$setting_value[$key]['btn']:[],
                'merchant_id' => isset($setting_value[$key]) && isset($setting_value[$key]['merchant_id'])?$setting_value[$key]['merchant_id']:"",
                'secret_key' => isset($setting_value[$key]) && isset($setting_value[$key]['secret_key'])?$setting_value[$key]['secret_key']:"",
                'status' => isset($setting_value[$key]) && isset($setting_value[$key]['status'])?$setting_value[$key]['status']:0,
                'start_week' => isset($setting_value[$key]) && isset($setting_value[$key]['start_week'])?$setting_value[$key]['start_week']:'',
                'end_week' => isset($setting_value[$key]) && isset($setting_value[$key]['end_week'])?$setting_value[$key]['end_week']:'',
                'during_time' => isset($setting_value[$key]) && isset($setting_value[$key]['during_time'])?$setting_value[$key]['during_time']:'',
                'private_key' => isset($setting_value[$key]) && isset($setting_value[$key]['private_key'])?$setting_value[$key]['private_key']:'',
                'public_key' => isset($setting_value[$key]) && isset($setting_value[$key]['public_key'])?$setting_value[$key]['public_key']:'',
            ];
        }
        $this->_data = $config;
    }

    public function setWithdrawConfig()
    {
        $type = $this->strInput('type');
        $max = $this->intInput('max');
        $min = $this->intInput('min');
        $secret_key = $this->strInput('secret_key');
        $merchant_id = $this->strInput('merchant_id');
        $private_key = $this->strInput('private_key');
        $public_key = $this->strInput('public_key');
        $start_week = $this->strInput('start_week');
        $end_week = $this->strInput('end_week');
        $during_time = $this->strInput('during_time');
        $status = $this->intInput('status');
        if ($max <= $min) {
            $this->_code = 403;
            $this->_msg = '最高限制应高于最低限制';
            return false;
        }
        $btn = request()->post('btn');
        $withdraw_type = config('pay.withdraw', []);
        if (!isset($withdraw_type[$type])) {
            $this->_code = 403;
            $this->_msg = '提现类型不支持';
            return false;
        }
        $setting_value = $this->SettingRepository->getWithdraw();
        $config = [];
        foreach ($withdraw_type as $key => $item) {
            if ($key == $type) {
                $config[$key] = [
                    'type' => $key,
                    'limit' => ['max'=>$max,'min'=>$min],
                    'btn' => array_values($btn),
                    'merchant_id' => $merchant_id,
                    'secret_key' => $secret_key,
                    'status' => $status,
                    'start_week' => $start_week,
                    'end_week' => $end_week,
                    'during_time' => $during_time,
                    'private_key' => $private_key,
                    'public_key' => $public_key
                ];
            } else {
                $config[$key] = [
                    'type' => $key,
                    'limit' => isset($setting_value[$key])?$setting_value[$key]['limit']:['max'=>0,'min'=>0],
                    'btn' => isset($setting_value[$key])?$setting_value[$key]['btn']:[],
                    'merchant_id' => isset($setting_value[$key]) && isset($setting_value[$key]['merchant_id'])?$setting_value[$key]['merchant_id']:"",
                    'secret_key' => isset($setting_value[$key]) && isset($setting_value[$key]['secret_key'])?$setting_value[$key]['secret_key']:"",
                    'status' => isset($setting_value[$key]) && isset($setting_value[$key]['status'])?$setting_value[$key]['status']:0,
                    'start_week' => isset($setting_value[$key]) && isset($setting_value[$key]['start_week'])?$setting_value[$key]['start_week']:'',
                    'end_week' => isset($setting_value[$key]) && isset($setting_value[$key]['end_week'])?$setting_value[$key]['end_week']:'',
                    'during_time' => isset($setting_value[$key]) && isset($setting_value[$key]['during_time'])?$setting_value[$key]['during_time']:'',
                    'private_key' => isset($setting_value[$key]) && isset($setting_value[$key]['private_key'])?$setting_value[$key]['private_key']:'',
                    'public_key' => isset($setting_value[$key]) && isset($setting_value[$key]['public_key'])?$setting_value[$key]['public_key']:'',
                ];
            }
        }
        ##更新
        $res = $this->SettingRepository->saveSetting(SettingRepository::WITHDRAW_KEY, $config);

        if ($res === false) {
            $this->_code = 403;
            $this->_msg = '操作失败';
            return false;
        }
        $this->_msg = '操作成功';
        return true;
    }

    public function queryGroupLeaderRoleId()
    {
        $val = $this->SettingRepository->getSettingByKey(SettingRepository::GROUP_LEADER_ROLE_KEY);
        $this->_data = $val ? ["role_id"=>$val->setting_value['role_id']]: ["role_id"=>""] ;
        return true;
    }

    public function saveGroupLeaderRoleId()
    {
        $role_id = $this->intInput('role_id');
        ##判断角色是否存在
        if(!$this->SettingRepository->checkRole($role_id)){
            $this->_code = 401;
            $this->_msg = "角色不存在";
            return false;
        }
        $res = $this->SettingRepository->saveSetting(SettingRepository::GROUP_LEADER_ROLE_KEY, ['role_id' => $role_id]);
        if($res === false){
            $this->_code = 401;
            $this->_msg = "操作失败";
            return false;
        }
        $this->_msg = "操作成功";
        return true;
    }

    public function rechargeConfig()
    {
        $recharge_type = config('pay.recharge',[]);
        $setting_value = $this->SettingRepository->getRecharge();
        $config = [];
        foreach($recharge_type as $key){
            $config[] = [
                'type' => $key,
                'limit' => isset($setting_value[$key])?$setting_value[$key]['limit']:['max'=>0,'min'=>0],
                'btn' => isset($setting_value[$key])?$setting_value[$key]['btn']:[],
                'merchant_id' => isset($setting_value[$key]) && isset($setting_value[$key]['merchant_id'])?$setting_value[$key]['merchant_id']:"",
                'secret_key' => isset($setting_value[$key]) && isset($setting_value[$key]['secret_key'])?$setting_value[$key]['secret_key']:"",
                'status' => isset($setting_value[$key]) && isset($setting_value[$key]['status'])?$setting_value[$key]['status']:0,
                'private_key' => isset($setting_value[$key]) && isset($setting_value[$key]['private_key'])?$setting_value[$key]['private_key']:'',
                'public_key' => isset($setting_value[$key]) && isset($setting_value[$key]['public_key'])?$setting_value[$key]['public_key']:'',
            ];
        }
        $this->_data = $config;
    }

    public function setRechargeConfig()
    {
        $type = $this->strInput('type');
        $max = $this->intInput('max');
        $min = $this->intInput('min');
        $secret_key = $this->strInput('secret_key');
        $public_key = $this->strInput('public_key');
        $private_key = $this->strInput('private_key');
        $merchant_id = $this->strInput('merchant_id');
        $status = $this->intInput('status');
        if ($max <= $min) {
            $this->_code = 403;
            $this->_msg = '最高限制应高于最低限制';
            return false;
        }
        $btn = request()->post('btn');
        $recharge_type = config('pay.recharge', []);
        $recharge_type2 = array_flip($recharge_type);
        if (!isset($recharge_type2[$type])) {
            $this->_code = 403;
            $this->_msg = '充值类型不支持';
            return false;
        }
        $setting_value = $this->SettingRepository->getRecharge();
        $config = [];
        foreach ($recharge_type as $key) {
            if ($key == $type) {
                $config[$key] = [
                    'type' => $key,
                    'limit' => ['max'=>$max,'min'=>$min],
                    'btn' => array_values($btn),
                    'merchant_id' => $merchant_id,
                    'secret_key' => $secret_key,
                    'status' => $status,
                    'public_key' => $public_key,
                    'private_key' => $private_key
                ];
            } else {
                $config[$key] = [
                    'type' => $key,
                    'limit' => isset($setting_value[$key])?$setting_value[$key]['limit']:['max'=>0,'min'=>0],
                    'btn' => isset($setting_value[$key])?$setting_value[$key]['btn']:[],
                    'merchant_id' => isset($setting_value[$key]) && isset($setting_value[$key]['merchant_id'])?$setting_value[$key]['merchant_id']:"",
                    'secret_key' => isset($setting_value[$key]) && isset($setting_value[$key]['secret_key'])?$setting_value[$key]['secret_key']:"",
                    'status' => isset($setting_value[$key]) && isset($setting_value[$key]['status'])?$setting_value[$key]['status']:0,
                    'private_key' => isset($setting_value[$key]) && isset($setting_value[$key]['private_key'])?$setting_value[$key]['private_key']:'',
                    'public_key' => isset($setting_value[$key]) && isset($setting_value[$key]['public_key'])?$setting_value[$key]['public_key']:'',
                ];
            }
        }
        ##更新
        $res = $this->SettingRepository->saveSetting(SettingRepository::RECHARGE_KEY, $config);

        if ($res === false) {
            $this->_code = 403;
            $this->_msg = '操作失败';
            return false;
        }
        $this->_msg = '操作成功';
        return true;
    }

    public function h5AlertContent()
    {
        $loginAlertValue = $this->SettingRepository->getSettingValueByKey("login_alert");
        $logoutAlertValue = $this->SettingRepository->getSettingValueByKey("logout_alert");
        if($loginAlertValue){
            $loginAlertValue['content'] = getHtml($loginAlertValue['content']);
            $loginAlertValue['status'] = $loginAlertValue['status']?? 1;
        }else{
            $loginAlertValue = [
                'content' => '',
                'btn' => [
                    'left' => [
                        'text' => '',
                        'link' => ''
                    ],
                    'right' => [
                        'text' => '',
                        'link' => ''
                    ],
                ],
                'status' => 1
            ];
        }
        if($logoutAlertValue){
            $logoutAlertValue['content'] = getHtml($loginAlertValue['content']);
            $logoutAlertValue['status'] = $logoutAlertValue['status'] ?? 1;
        }
        $this->_data = compact('loginAlertValue','logoutAlertValue');
    }

    public function setH5AlertContent()
    {
        $type = $this->intInput('type',1);
        $content = $this->htmlInput('content');
        $status = $this->intInput('status',1);
        $key = "";
        switch ($type){
            case 1:
                $validator = Validator::make(request()->input(), [
                    'left.text' => ['required', 'between:2,20', 'alpha_dash'],
                    'right.text' => ['required', 'between:2,20', 'alpha_dash'],
                    'left.link' => ['required', 'url'],
                    'right.link' => ['required', 'url'],
                    'status' => ['required', 'integer', Rule::in(0,1)]
                ]);
                if($validator->fails()){
                    $this->_code = 403;
                    $this->_msg = $validator->errors()->first();
                    return false;
                }
                $data = [
                    'content' => $content,
                    'btn' => [
                        'left' => [
                            'text' => $this->strInput('left.text'),
                            'link' => $this->strInput('left.link')
                        ],
                        'right' => [
                            'text' => $this->strInput('right.text'),
                            'link' => $this->strInput('right.link')
                        ]
                    ],
                    'status' => $status
                ];
                $key = "login_alert";
                break;
            case 2:
                $data = compact('content','status');
                $key = "logout_alert";
                break;
            default:
                $data = [];
                break;
        }
        $res = $this->SettingRepository->saveSetting($key, $data);
        if($res === false){
            $this->_code = 403;
            $this->_msg = "操作失败";
            return false;
        }
        $this->_msg = "操作成功";
        return true;
    }

    public function serviceEdit()
    {
        $btn_1 = request()->post('btn_1');
        $btn_2 = request()->post('btn_2');
        $status = request()->post('status');
        $service = [
            'btn_1' => [
                'link' => strip_tags($btn_1['link']),
                'title' => strip_tags($btn_1['title']),
                'icon' => strip_tags($btn_1['icon']),
            ],
            'btn_2' => [
                'link' => strip_tags($btn_2['link']),
                'title' => strip_tags($btn_2['title']),
                'icon' => strip_tags($btn_2['icon']),
            ],
            'status' => $status
        ];
        if ($status == 1) {
            $crisp = $this->SettingRepository->getSettingValueByKey(SettingDic::key('CRISP_WEBSITE_ID'));
            if ($crisp && array_key_exists('status', $crisp) && $crisp['status']) {
                $crisp['status'] = 0;
                $this->SettingRepository->saveSetting(SettingDic::key('CRISP_WEBSITE_ID'), $crisp);
            }
        }
        $res = $this->SettingRepository->saveSetting(SettingDic::key('SERVICE'), $service);
        if($res === false){
            $this->_code = 403;
            $this->_msg = '修改失败';
             return false;
        }
        $this->_msg = '修改成功';
        return true;
    }

    public function getService()
    {
        $data = $this->SettingRepository->getSettingValueByKey(SettingDic::key('SERVICE'));
        if(!$data){
            $data = [
                'btn_1' => [
                    'link' => '',
                    'title' => '',
                    'icon' => ''
                ],
                'btn_2' => [
                    'link' => '',
                    'title' => '',
                    'icon' => ''
                ],
                'status' => 0
            ];
        }
        $this->_data = $data;
    }

    public function getCrisp()
    {
        $data = $this->SettingRepository->getSettingValueByKey(SettingDic::key('CRISP_WEBSITE_ID'));
        if (!$data){
            $data = [
                'status' => 0,
                'crisp_website_id' => ''
            ];
        }
        $this->_data = $data;
    }

    public function crispSave()
    {
        $status = request()->post('status');
        $crisp_website_id = request()->post('crisp_website_id', '');
        $crisp = [
            'status' => $status,
            'crisp_website_id' => $crisp_website_id,
        ];
        if ($status == 1) {
            $service = $this->SettingRepository->getSettingValueByKey(SettingDic::key('SERVICE'));
            if ($service && array_key_exists('status', $service) && $service['status']) {
                $service['status'] = 0;
                $this->SettingRepository->saveSetting(SettingDic::key('SERVICE'), $service);
            }
        }
        $res = $this->SettingRepository->saveSetting(SettingDic::key('CRISP_WEBSITE_ID'), $crisp);
        if($res === false){
            $this->_code = 403;
            $this->_msg = '修改失败';
            return false;
        }
        $this->_msg = '修改成功';
        return true;
    }

    public function getApp()
    {
        $data = $this->SettingRepository->getSettingValueByKey(SettingDic::key('DOWNLOAD_APP'));
        if (!$data){
            $data = [
                'status' => 0,
                'link' => ''
            ];
        }
        $this->_data = $data;
    }

    public function appSave():bool
    {
        $status = request()->post('status');
        $link = request()->post('link', '');
        $app = [
            'status' => $status,
            'link' => $link,
        ];
        $res = $this->SettingRepository->saveSetting(SettingDic::key('DOWNLOAD_APP'), $app);
        if($res === false){
            $this->_code = 403;
            $this->_msg = '修改失败';
            return false;
        }
        $this->_msg = '修改成功';
        return true;
    }

    public function getAboutUs()
    {
        $key = $this->getAboutUsKey();
        $data = $this->SettingRepository->getSettingValueByKey(SettingDic::key($key));
        if (!$data){
            $data = [
                'title' => '',
                'content' => ''
            ];
        }else{
            $data['content'] = htmlspecialchars_decode($data['content']);
        }
        $this->_data = $data;
    }

    public function aboutUsSave():bool
    {
        $title = $this->strInput('title');
        $content = $this->htmlInput('content');
        $data = compact('title','content');
        $key = $this->getAboutUsKey();
        $res = $this->SettingRepository->saveSetting(SettingDic::key($key), $data);
        if($res === false){
            $this->_code = 403;
            $this->_msg = '修改失败';
            return false;
        }
        $this->_msg = '修改成功';
        return true;
    }

    public function getAboutUsKey():string
    {
        $type = $this->intInput('type');
        switch($type){
            case 1://Privacy Policy
                $key = 'PRIVACY_POLICY';
                break;
            case 2:
                $key = 'RISK_DISCLOSURE_AGREEMENT';
                break;
            case 3:
                $key = 'ABOUT_US';
                break;
            default:
                $key = '';
                break;
        }
        return $key;
    }

    public function getActivity()
    {
        $data = $this->SettingRepository->getSettingValueByKey(SettingDic::key('ACTIVITY'));
        if (!$data){
            $data = [
                'give_away_red_envelopes_status' => 1,
            ];
        }
        $this->_data = $data;
    }

    public function activitySave(): bool
    {
        $give_away_red_envelopes_status = $this->intInput('give_away_red_envelopes_status',1);
        $data = compact('give_away_red_envelopes_status');
        $key = 'ACTIVITY';
        $res = $this->SettingRepository->saveSetting(SettingDic::key($key), $data);
        if($res === false){
            $this->_code = 403;
            $this->_msg = '修改失败';
            return false;
        }
        $this->_msg = '修改成功';
        return true;
    }

    public function getRechargeRebate()
    {
        $data = $this->SettingRepository->getSettingValueByKey(SettingDic::key('RECHARGE_REBATE'));
        if (!$data){
            $data = [
                'status' => 0,
                'percent' => 0,
                'min_recharge' => 0,
                'max_rebate' => 0
            ];
        }
        $this->_data = $data;
    }

    public function rechargeRebateSave():bool
    {
        $status = $this->intInput('status');
        $percent = $this->floatInput('percent');
        $max_rebate = $this->intInput('max_rebate');
        $min_recharge = $this->intInput('min_recharge');
        $data = compact('status','percent','max_rebate','min_recharge');
        $res = $this->SettingRepository->saveSetting(SettingDic::key('RECHARGE_REBATE'), $data);
        if($res === false){
            $this->_code = 403;
            $this->_msg = '修改失败';
            return false;
        }
        $this->_msg = '修改成功';
        return true;
    }

    public function getWithdrawSafe()
    {
        $data = $this->SettingRepository->getSettingValueByKey(SettingDic::key('WITHDRAW_SAFE'));
        if (!$data){
            $data = [
                'limit' => 0,
                'password' => 'eyJpdiI6IkxGdS9RNXN6T0xtbGJNdkVSTXVrWlE9PSIsInZhbHVlIjoiNEd2S3JFNnpLdW1tUFBUU2pWNmZxQT09IiwibWFjIjoiZTg3MGY5ZTBjOTRjYWJlMDIwNjg0ZmRmZmMxYTYwZDg0MDIwMjU1NGU0NjNmNDUxMWM2YTRiNzlmNTcxYTRmNSJ9'
            ];
        }
        $this->_data = $data;
    }

    public function withdrawSafeSave():bool
    {
        $limit = $this->floatInput('limit');
        $this->getWithdrawSafe();
        $conf = $this->_data;
        $old_password = $this->strInput('old_password');
        $password = $this->strInput('password');
        if($old_password)
        {
            if(Crypt::decrypt($conf['password']) != $old_password)
            {
                $this->_code = 401;
                $this->_msg = '原密码错误';
                return false;
            }
            if($old_password == $password)
            {
                $this->_code = 401;
                $this->_msg = '新密码不能与旧密码相同';
                return false;
            }
            if(!$password || strlen($password) < 6)
            {
                $this->_code = 401;
                $this->_msg = '新密码至少6位数';
                return false;
            }
            $conf['password'] = Crypt::encrypt($password);
        }
        $conf['limit'] = $limit;
        $res = $this->SettingRepository->saveSetting(SettingDic::key('WITHDRAW_SAFE'), $conf);
        $this->_data = [];
        if($res === false){
            $this->_code = 403;
            $this->_msg = '修改失败';
            return false;
        }
        $this->_msg = '修改成功';
        return true;
    }

    public function getRegister()
    {
        $data = $this->SettingRepository->getSettingValueByKey(SettingDic::key('REGISTER'));
        if (!$data){
            $data = [
                'status' => 0,
                'rebate' => 0
            ];
        }
        if(!isset($data['is_leader_limit']))$data['is_leader_limit'] = 0;
        $this->_data = $data;
    }

    public function registerSave():bool
    {
        $status = $this->intInput('status');
        $rebate = $this->intInput('rebate');
        $is_leader_limit = $this->intInput('is_leader_limit');
        $data = compact('status','rebate','is_leader_limit');
        $res = $this->SettingRepository->saveSetting(SettingDic::key('REGISTER'), $data);
        if($res === false){
            $this->_code = 403;
            $this->_msg = '修改失败';
            return false;
        }
        $this->_msg = '修改成功';
        return true;
    }

    public function getInviteFriends()
    {
        $data = $this->SettingRepository->getSettingValueByKey(SettingDic::key('INVITE_FRIENDS'));
        if (!$data){
            $data = [
                'image_id' => 0,
                'image_url' => ''
            ];
        }
        $this->_data = $data;
    }

    public function inviteFriendsSave(): bool
    {
        $image_id = $this->intInput('image_id');
        $image = $this->UploadsRepository->getImage($image_id);
        $image_url = $image['path_url'];
        $data = compact('image_id','image_url');
        $res = $this->SettingRepository->saveSetting(SettingDic::key('INVITE_FRIENDS'), $data);
        if($res === false){
            $this->_code = 403;
            $this->_msg = '修改失败';
            return false;
        }
        $this->_msg = '修改成功';
        return true;
    }

    public function signSetting()
    {
        $data = $this->SettingRepository->getSettingValueByKey(SettingDic::key('SIGN_SETTING'));
        if (!$data){
            $data = [
                'image_id' => 0,
                'image_url' => '',
                'status' => 0
            ];
        }
        $this->_data = $data;
    }

    public function signSettingSave(): bool
    {
        return $this->editActivity(SettingDic::key('SIGN_SETTING'));
    }

    public function redEnvelopeTask()
    {
        $data = $this->SettingRepository->getSettingValueByKey(SettingDic::key('RED_ENVELOPE_TASK'));
        if (!$data){
            $data = [
                'image_id' => 0,
                'image_url' => '',
                'status' => 0
            ];
        }
        $this->_data = $data;
    }

    public function redEnvelopeTaskSave(): bool
    {
        return $this->editActivity(SettingDic::key('RED_ENVELOPE_TASK'));
    }

    public function editActivity($key): bool
    {
        $status = $this->intInput('status');
        $image_id = $this->intInput('image_id');
        $image = $this->UploadsRepository->getImage($image_id);
        $image_url = $image['path_url'];
        $data = compact('status','image_id','image_url');
        $res = $this->SettingRepository->saveSetting($key, $data);
        if($res === false){
            $this->_code = 403;
            $this->_msg = '修改失败';
            return false;
        }
        $this->_msg = '修改成功';
        return true;
    }

}
