<?php


namespace App\Services\Admin;


use App\Repositories\Admin\GameRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GameService extends BaseService
{

    protected $GameRepository;

    public function __construct
    (
        GameRepository $gameRepository
    )
    {
        $this->GameRepository = $gameRepository;
    }

    public function cateList()
    {
        try{
            $validator = Validator::make(request()->input(),
                [
                    'page' => 'gte:1',
                ]
            );
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $where = [
                'is_rg' => ['=', 0],
                'pid' => ['=', 0]
            ];
            $this->_data = $this->GameRepository->cateList($where);
        }catch(\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

    public function gameCateList()
    {
        try{
            $where = [
                'is_rg' => ['=', 0],
                'pid' => ['=', 0]
            ];
            $this->_data = $this->GameRepository->gameCateList($where);
        }catch(\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

    public function parentCateList()
    {
        try{
            $validator = Validator::make(request()->input(),
                [
                    'page' => 'gte:1',
                ]
            );
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $where = [
                'is_rg' => ['=', 0],
                'pid' => ['=', 0]
            ];
            $this->_data = $this->GameRepository->parentCateList($where);
        }catch(\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

    public function addCate()
    {
        try{
            $validator = Validator::make(request()->input(), $this->cateHandleRule());
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $is_rg = $this->intInput('is_rg',0);
            $res = $this->GameRepository->addCate(array_merge($this->cateFilterData(), ['is_rg'=>$is_rg]));
            if(!$res)
            {
                throw new \Exception('操作失败');
            }
        }catch (\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

    public function editCate()
    {
        try{
            $validator = Validator::make(request()->input(), $this->cateHandleRule(2));
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $id = $this->intInput('id');
            $res = $this->GameRepository->editCate($id, $this->cateFilterData());
            if(!$res)
            {
                throw new \Exception('操作失败');
            }
        }catch (\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

    public function delCate()
    {
        $this->GameRepository->delCate($this->intInput('id'));
    }

    public function cateDetail()
    {
        $where = [
            'is_rg' => ['=', 1]
        ];
        $this->_data = $this->GameRepository->cateDetail($where);
    }

    protected function cateHandleRule($flag=1): array
    {
        $rule = [
            'label' => 'required|max:16|min:2',
            'icon' => 'required',
            'status' => Rule::in(0,1),
            'sort' => 'gte:0|lte:9999',
            'pid' => 'gte:0',
        ];
        if($flag == 2)
        {
            $rule['id'] = "required|gte:1";
        }
        return $rule;
    }

    protected function cateFilterData(): array
    {
        return [
            'label' => $this->strInput('label'),
            'icon' => $this->strInput('icon'),
            'status' => $this->intInput('status'),
            'sort' => $this->intInput('sort'),
            'pid' => $this->intInput('pid')
        ];
    }

    public function gameList()
    {
        try{
            $validator = Validator::make(request()->input(),
                [
                    'page' => 'gte:1',
                    'size' => Rule::in(10,20,30,40),
                    'cid' => 'integer|gte:0',
                    'status' => Rule::in(-1,0,1)
                ]
            );
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $where = [];
            $size = $this->sizeInput();
            $cid = $this->intInput('cid');
            if($cid)
            {
                $cids = $this->GameRepository->cateGetChildren($cid);
                $where['cid'] = ['in', $cids];
            }
            $status = $this->intInput('status',-1);
            if($status >= 0)
            {
                $where['status'] = ['=', $status];
            }
            $this->_data = $this->GameRepository->gameList($where, $size);
        }catch(\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

    public function addGame()
    {
        try{
            $validator = Validator::make(request()->input(), $this->gameHandleRule());
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $this->_data = $this->GameRepository->addGame($this->gameFilterData());
        }catch(\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

    public function editGame()
    {
        try{
            $validator = Validator::make(request()->input(), $this->gameHandleRule(2));
            if($validator->fails())
            {
                throw new \Exception($validator->errors()->first());
            }
            $id = $this->intInput('id');
            $this->_data = $this->GameRepository->editGame($this->gameFilterData(), $id);
        }catch(\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
        }
    }

    protected function gameHandleRule($flag=1): array
    {
        $rule = [
            'label' => 'required|max:16|min:2',
            'icon' => 'required',
            'status' => Rule::in(0,1),
            'sort' => 'gte:0|lte:9999',
            'cid' => 'required',
        ];
        if($flag == 2)
        {
            $rule['id'] = "required|gte:1";
        }
        return $rule;
    }

    protected function gameFilterData(): array
    {
        $cid = request()->input('cid');
        if(is_array($cid))$cid = $cid[1];
        return [
            'label' => $this->strInput('label'),
            'icon' => $this->strInput('icon'),
            'status' => $this->intInput('status'),
            'sort' => $this->intInput('sort'),
            'cid' => $cid,
            'link' => $this->strInput('link'),
            'other' => $this->strInput('other'),
        ];
    }

    public function delGame()
    {
        $this->GameRepository->delGame($this->intInput('id'));
    }

}
