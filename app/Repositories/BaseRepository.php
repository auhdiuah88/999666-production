<?php


namespace App\Repositories;


use App\Models\Cx_User;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository
{
    private $list = [];

    public function getUserIds($phone)
    {
        $ids = Cx_User::where("phone", "like", "%" . $phone . "%")->get("id")->toArray();
        return array_column($ids, "id");
    }


    protected function whereCondition($data, $model)
    {
        $this->list = $data["conditions"];
        $ops = $data["ops"];
        foreach ($ops as $index => $op) {
            switch ($op) {
                case "between":
                    $model->where($this->betweenCondition($index));
                    break;
                case "like":
                    $model->where($this->likeCondition($index));
                    break;
                case ">":
                    $model->where($this->greaterCondition($index));
                    break;
                case "<":
                    $model->where($this->lessCondition($index));
                    break;
                case "in":
                    $model->where($this->inCondition($index));
                    break;
                default:
                    $model->where($this->equalCondition($index));
            }
        }
        dd($model->toSql());
        return $model;
    }

    /**
     * 如果是in数组的条件拼装
     * @param $key
     * @return \Closure
     */
    public function inCondition($key)
    {
        if (!array_key_exists($key, $this->list)) {
            return null;
        }
        $value = $this->list[$key];
        if (is_null($value)) {
            return null;
        }
        return function ($query) use ($key, $value) {
            $query->whereIn($key, $value);
        };
    }

    public function renderWhere()
    {
        return function ($query) {
            $query->whereRaw("1 = 1");
        };
    }

    /**
     * 如果是小于的条件拼装
     * @param $key
     * @return \Closure
     */
    public function lessCondition($key)
    {
        if (!array_key_exists($key, $this->list)) {
            return null;
        }
        $value = $this->list[$key];
        if (is_null($value)) {
            return null;
        }
        return function ($query) use ($key, $value) {
            $query->where($key, "<", $value);
        };
    }

    /**
     * 如果是大于的条件拼装
     * @param $key
     * @return \Closure
     */
    public function greaterCondition($key)
    {
        if (!array_key_exists($key, $this->list)) {
            return null;
        }
        $value = $this->list[$key];
        if (is_null($value)) {
            return null;
        }
        return function ($query) use ($key, $value) {
            $query->where($key, ">", $value);
        };
    }

    /**
     * 如果是时间区间的条件拼装
     * @param $key
     * @return \Closure
     */
    private function betweenCondition($key)
    {
        if (!array_key_exists($key, $this->list)) {
            return null;
        }
        $value = $this->list[$key];
        if (is_null($value)) {
            return null;
        }
        return function ($query) use ($key, $value) {
            $query->whereBetween($key, $value);
        };
    }

    /**
     * 如果是相等的条件拼装
     * @param $key
     */
    private function equalCondition($key)
    {
        if (!array_key_exists($key, $this->list)) {
            return null;
        }
        $value = $this->list[$key];
        if (is_null($value)) {
            return null;
        }
        return function ($query) use ($key, $value) {
            $query->where($key, $value);
        };
    }

    /**
     * 如果是模糊查询的条件拼装
     * @param $key
     */
    private function likeCondition($key)
    {
        if (!array_key_exists($key, $this->list)) {
            return null;
        }
        $value = $this->list[$key];
        if (is_null($value)) {
            return null;
        }
        return function ($query) use ($key, $value) {
            $query->where($key, "like", "%" . $value . "%");
        };
    }
}
