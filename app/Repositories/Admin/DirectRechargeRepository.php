<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Direct_Recharge_logs;
use App\Models\Cx_User;

class DirectRechargeRepository
{

    protected $Cx_Direct_Recharge_logs, $Cx_User;

    public function __construct
    (
        Cx_Direct_Recharge_logs $cx_Direct_Recharge_logs,
        Cx_User $cx_User
    )
    {
        $this->Cx_Direct_Recharge_logs = $cx_Direct_Recharge_logs;
        $this->Cx_User = $cx_User;
    }

    /**
     * 申请列表
     * @param $where
     * @param $size
     * @return mixed
     */
    public function lists($where, $size)
    {
        if(isset($where['phone']) && $where['phone'])
        {
            $user_id = $this->Cx_User->where('phone', '=', $where['phone'])->value('id');
            $where['user_id'] = ['=', $user_id?:0];
            unset($where['phone']);
        }
        return makeModel($where, $this->Cx_Direct_Recharge_logs)
            ->with(
                [
                    'user' => function($query)
                    {
                        $query->select(['id', 'phone']);
                    },
                    'image' => function($query)
                    {
                        $query->select(['image_id', 'file_path']);
                    },
                    'bank' => function($query)
                    {
                        $query->select(['id', 'bank_name', 'bank_card_account']);
                    }
                ]
            )
            ->orderByDesc('created_at')
            ->paginate($size);
    }

    public function getInfoById($id)
    {
        return $this->Cx_Direct_Recharge_logs->where('id', '=', $id)->first();
    }

    public function pass($data)
    {
        return $this->update(array_merge($data, ['exam_time' => time(), 'status' => 1]));
    }

    public function refuse($data)
    {
        return $this->update(array_merge($data, ['exam_time' => time(), 'status' => 2]));
    }

    public function update($data)
    {
        return $this->Cx_Direct_Recharge_logs
            ->where("id", "=", $data['id'])
            ->update($data);
    }

}
