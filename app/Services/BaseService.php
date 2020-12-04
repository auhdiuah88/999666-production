<?php


namespace App\Services;


use App\Models\Cx_User;
use App\Repositories\Admin\FirstChargeRepository;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

abstract class BaseService
{
    public $_code = 200;

    public $_msg = "查询成功";

    public $_data = [];

    public function getUserId($token)
    {
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        return $data[0];
    }

    public function getUserIds($data)
    {
        $conditions = $data["conditions"];
        $ops = $data["ops"];
        DB::connection()->enableQueryLog();
        $this->getConditions($data, new Cx_User())->get("id")->toArray();
        dd(DB::getQueryLog());
        $ids = array_column(, "id");
        if (array_key_exists("phone", $conditions)) {
            unset($data["conditions"]["phone"]);
            $data["conditions"]["user_id"] = $ids;
            unset($data["ops"]["phone"]);
            $data["conditions"]["user_id"] = "in";
        }

        if (array_key_exists("reg_source_id", $conditions)) {
            unset($data["conditions"]["reg_source_id"]);
            $data["conditions"]["user_id"] = $ids;
            unset($data["ops"]["reg_source_id"]);
            $data["ops"]["user_id"] = "in";
        }
        return $data;
    }

    public function getConditions($data, $model)
    {
        if (array_key_exists("phone", $data["conditions"])) {
            if ($data["conditions"]["phone"]) {
                $model = $model->where(function ($query) use ($data) {
                    $query->where("phone", "like", "%" . $data["conditions"]["phone"] . "%");
                });
            }
        }

        if (array_key_exists("reg_source_id", $data["conditions"])) {
            if ($data["conditions"]["reg_source_id"]) {
                $model = $model->where(function ($query) use ($data) {
                    $query->where("reg_source_id", $data["conditions"]["reg_source_id"]);
                });
            }
        }

        return $model;
    }
}
