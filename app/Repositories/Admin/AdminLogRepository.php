<?php

namespace App\Repositories\Admin;


use App\Models\Cx_Admin_Operation_Log;
use Illuminate\Support\Facades\DB;

class AdminLogRepository
{
    /**
     * @var Cx_Admin_Operation_Log
     */
    private $cx_Admin_Operation_Log;

    public function __construct(Cx_Admin_Operation_Log $cx_Admin_Operation_Log)
    {
        $this->cx_Admin_Operation_Log = $cx_Admin_Operation_Log;
    }

    /**
     * @param $offset
     * @param $limit
     * @return mixed
     */
    public function list($offset, $limit)
    {
        $ids = $this->getCurPageIds($offset, $limit);
        return $this->cx_Admin_Operation_Log
            ->with([
                'admin_user' => function ($query){
                    $query->select(["id", "username"]);
                }
            ])
            ->select('id','exec_time', 'path', 'method', 'ip', 'admin_id', 'c_time')
            ->whereIn('log.id', $ids)
            ->orderByDesc('log.id')
            ->get()
            ->toArray();
    }

    public function getCurPageIds($offset, $limit)
    {
        return array_column($this->initModel()->select('id')->orderByDesc('id')->offset($offset)->limit($limit)->get()->toArray(), 'id');
    }

    public function getCount()
    {
        return $this->initModel()->count();
    }

    public function initModel()
    {
        return $this->setTimeSearch($this->cx_Admin_Operation_Log);
    }

    public function setTimeSearch($model)
    {
        if (request()->has('time_start')){
            $model = $model->where('c_time', '>=', request()->get('time_start'));
        }

        if (request()->has('time_end')){
            $model = $model->where('c_time', '<', request()->get('time_end'));
        }
        return $model;
    }
}
