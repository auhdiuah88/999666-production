<?php


namespace App\Services\Admin;


use App\Dictionary\SettingDic;
use App\Repositories\Admin\SettingRepository;
use App\Repositories\Admin\SystemRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SystemService extends BaseService
{
    private $SystemRepository, $SettingRepository;

    public function __construct
    (
        SystemRepository $systemRepository,
        SettingRepository $settingRepository
    )
    {
        $this->SystemRepository = $systemRepository;
        $this->SettingRepository = $settingRepository;
    }

    public function findAll()
    {
        $data = $this->SystemRepository->findAll();
        $ip_switch = $this->SettingRepository->getSettingValueByKey(SettingDic::key('IP_SWITCH'),false);
        if($ip_switch){
            $data->ip_switch = $ip_switch;
        }else{
            $data->ip_switch = [
                'ip_switch' => 0
            ];
        }
        $is_check_recharge = $this->SettingRepository->getSettingValueByKey(SettingDic::key('IS_CHECK_RECHARGE'),false);
        if($is_check_recharge){
            $data->is_check_recharge = $is_check_recharge;
        }else{
            $data->is_check_recharge = [
                'is_check_recharge' => 0
            ];
        }
        $logo = $this->SettingRepository->getSettingValueByKey(SettingDic::key('LOGO'),false);
        if($logo){
            $logo_url = DB::table('uploads')->where("image_id",$logo['logo'])->value("path");
            $data->logo = [
                'logo' => $logo['logo'],
                'logo_url' => URL::asset($logo_url),
            ];
        }else{
            $data->logo = [
                'logo' => 0,
                'logo_url' => '',
            ];
        }
        $this->_data = $data;
    }

    public function editSystem($data)
    {
        $ipSwitch = $data['ipSwitch']??0;
        $isCheckRecharge = $data['isCheckRecharge']??0;
        $logo = $data['logo']??0;
        if(isset($data['ipSwitch']))unset($data['ipSwitch']);
        if(isset($data['isCheckRecharge']))unset($data['isCheckRecharge']);
        if(isset($data['logo']))unset($data['logo']);
        ##编辑setting
        $this->SettingRepository->saveSetting(SettingDic::key('IP_SWITCH'), ['ip_switch'=>$ipSwitch]);
        $this->SettingRepository->saveSetting(SettingDic::key('IS_CHECK_RECHARGE'), ['is_check_recharge'=>$isCheckRecharge]);
        $this->SettingRepository->saveSetting(SettingDic::key('LOGO'), ['logo'=>$logo]);
        if ($this->SystemRepository->editSystem($data)) {
            $this->_msg = "编辑成功";
        } else {
            $this->_code = 402;
            $this->_msg = "编辑失败";
        }
    }

    public function tipsList()
    {
        try{
            ##参数验证
            $validator = Validator::make(request()->input(), [
                'page' => 'required|gte:1',
                'status' => Rule::in(0,1,-1)
            ]);
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $status = $this->intInput('status',-1);
            $where = [];
            if($status > -1)
            {
                $where['status'] = ['=', $status];
            }
            $size = $this->sizeInput();
            $this->_data = $this->SystemRepository->tipsList($where, $size);
        }catch(\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

    public function addTips()
    {
        try{
            ##参数验证
            $validator = Validator::make(request()->input(), [
                'content' => 'required|max:255|min:5',
                'status' => Rule::in(0,1)
            ]);
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $data = [
                'content' => $this->htmlInput('content'),
                'status' => $this->intInput('status'),
                'start_time' => $this->intInput('start_time'),
                'end_time' => $this->intInput('end_time')
            ];
            if($data['end_time'] <= $data['start_time'])
            {
                throw new \Exception('结束时间应该大于开始时间');
            }
            if($data['end_time'] <= time())
            {
                throw new \Exception('结束时间不能早于当前时间');
            }
            $res = $this->SystemRepository->addTips($data);
            if(!$res)
            {
                throw new \Exception('操作失败');
            }
        }catch(\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

    public function editTips()
    {
        try{
            ##参数验证
            $validator = Validator::make(request()->input(), [
                'id' => 'required|gte:1',
                'content' => 'required|max:255|min:5',
                'status' => Rule::in(0,1)
            ]);
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $id = $this->intInput('id');
            $data = [
                'content' => $this->htmlInput('content'),
                'status' => $this->intInput('status'),
                'start_time' => $this->intInput('start_time'),
                'end_time' => $this->intInput('end_time')
            ];
            if($data['end_time'] <= $data['start_time'])
            {
                throw new \Exception('结束时间应该大于开始时间');
            }
            if($data['end_time'] <= time())
            {
                throw new \Exception('结束时间不能早于当前时间');
            }
            $res = $this->SystemRepository->editTips($id, $data);
            if(!$res)
            {
                throw new \Exception('操作失败');
            }
        }catch(\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

    public function delTips()
    {
        try{
            ##参数验证
            $validator = Validator::make(request()->input(), [
                'id' => 'required|gte:1',
            ]);
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $id = $this->intInput('id');
            $res = $this->SystemRepository->delTips($id);
            if(!$res)
            {
                throw new \Exception('操作失败');
            }
        }catch(\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

    public function adsList()
    {
        try{
            ##参数验证
            $validator = Validator::make(request()->input(), [
                'page' => 'required|gte:1',
                'status' => Rule::in(0,1,-1)
            ]);
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $status = $this->intInput('status',-1);
            $where = [];
            if($status > -1)
            {
                $where['status'] = ['=', $status];
            }
            $size = $this->sizeInput();
            $this->_data = $this->SystemRepository->adsList($where, $size);
        }catch(\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

    public function addAds()
    {
        try{
            ##验证
            $validator = Validator::make(request()->input(), $this->handleAdsRules());
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $res = $this->SystemRepository->addAds($this->handleAdsData());
            if(!$res)
            {
                throw new \Exception('创建失败');
            }
        }catch(\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

    public function editAds()
    {
        try{
            ##验证
            $validator = Validator::make(request()->input(), $this->handleAdsRules(2));
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $id = $this->intInput('id');
            $res = $this->SystemRepository->editAds($id, $this->handleAdsData());
            if(!$res)
            {
                throw new \Exception('更新失败');
            }
        }catch(\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

    protected function handleAdsRules($flag=1): array
    {
        $rule = [
            'title' => 'required|max:50',
            'content' => 'required',
            'status' => Rule::in(0,1),
        ];
        if($flag == 2)
        {
            $rule['id'] = 'required|gte:1';
        }
        return $rule;
    }

    protected function handleAdsData(): array
    {
        return [
            'title' => $this->strInput('title'),
            'content' => $this->htmlInput('content'),
            'status' => $this->intInput('status')
        ];
    }

    public function delAds()
    {
        try{
            ##验证
            $validator = Validator::make(request()->input(),
                [
                    'id' => 'required|gte:1'
                ]
            );
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $id = $this->intInput('id');
            $res = $this->SystemRepository->delAds($id);
            if(!$res)
            {
                throw new \Exception('删除失败');
            }
        }catch(\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

}
