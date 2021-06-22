<?php


namespace App\Services\Api;


use App\Repositories\Api\IndexRepository;
use Illuminate\Support\Facades\Validator;

class IndexService extends BaseService
{

    protected $IndexRepository;

    public function __construct
    (
        IndexRepository $indexRepository
    )
    {
        $this->_code = 200;
        $this->_msg = '';
        $this->IndexRepository = $indexRepository;
    }

    public function tips()
    {
        $where = [
            'status' => ['=', 1],
            'start_time' => ['<', time()],
            'end_time' => ['>', time()]
        ];
        ##获取通知列表
        $this->_data = $this->IndexRepository->tips($where);
    }

    public function gameCateList()
    {
        $where = [
            'pid' => ['=', 0],
            'status' => ['=', 1]
        ];
        $this->_data = $this->IndexRepository->gameCateList($where);
    }

    public function cateDetail()
    {
        try{
            ##验证
            $validator = Validator::make(request()->input(), [
                'cid' => 'required|integer|gte:1'
            ]);
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $cid = request('cid',0);
            ##查询分类
            $cInfo = $this->IndexRepository->getGameCateInfoById($cid);
            if(empty($cInfo) || $cInfo->pid != 0)
            {
                throw new \Exception('category not exist');
            }
            if($cInfo->is_rg == 1)
            {
                $this->_data = $this->IndexRepository->gameRecords();
            }else{
                $this->_data = $this->IndexRepository->cateDetail($cid);
            }
        }catch(\Exception $e){
            $this->_code = 414;
            $this->_msg = $e->getMessage();
        }
    }

    public function adsDetail()
    {
        try{
            ##验证
            $validator = Validator::make(request()->input(),
                [
                    'id' => 'required|integer|gte:1'
                ]
            );
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $id = request('id',0);
            $info = $this->IndexRepository->adsDetail($id);
            if(!$info)
            {
                throw new \Exception('article not exist');
            }
            if($info->status != 1)
            {
                throw new \Exception('the article has been taken off ');
            }
            $this->_data = $info;
        }catch(\Exception $e){
            $this->_code = 414;
            $this->_msg = $e->getMessage();
        }
    }

}
