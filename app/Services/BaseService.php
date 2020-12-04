<?php


namespace App\Services;


use App\Models\Cx_User;
use App\Repositories\Admin\FirstChargeRepository;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

abstract class BaseService
{
    public $_code = 200;

    public $_msg = "success";

    public $_data = [];

    public function getUserId($token)
    {
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        return $data[0];
    }

    public function getUserIds($data, $key)
    {
        $conditions = $data["conditions"];
        $ops = $data["ops"];
        $ids = array_column($this->getConditions($data, new Cx_User())->get("id")->toArray(), "id");
        if (array_key_exists("phone", $conditions)) {
            unset($data["conditions"]["phone"]);
            $data["conditions"][$key] = $ids;
            unset($data["ops"]["phone"]);
            $data["ops"][$key] = "in";
        }

        if (array_key_exists("reg_source_id", $conditions)) {
            unset($data["conditions"]["reg_source_id"]);
            $data["conditions"][$key] = $ids;
            unset($data["ops"]["reg_source_id"]);
            $data["ops"][$key] = "in";
        }
        return $data;
    }

    public function getConditions($data, $model)
    {
        if (array_key_exists("phone", $data["conditions"])) {
            if (!is_null($data["conditions"]["phone"])) {
                $model = $model->where(function ($query) use ($data) {
                    $query->where("phone", "like", "%" . $data["conditions"]["phone"] . "%");
                });
            }
        }

        if (array_key_exists("reg_source_id", $data["conditions"])) {
            if (!is_null($data["conditions"]["reg_source_id"])) {
                $model = $model->where(function ($query) use ($data) {
                    $query->where("reg_source_id", $data["conditions"]["reg_source_id"]);
                });
            }
        }

        return $model;
    }


}
