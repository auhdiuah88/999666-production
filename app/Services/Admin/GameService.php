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
                'is_rg' => ['=', 0]
            ];
            $this->_data = $this->GameRepository->cateList($where);
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

}
