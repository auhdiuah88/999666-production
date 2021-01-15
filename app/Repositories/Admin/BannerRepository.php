<?php
/**
 * Created by PhpStorm.
 * User: ck
 * Date: 2021-01-12
 * Time: 16:31
 */

namespace App\Repositories\Admin;


use App\Models\Cx_Banner;

class BannerRepository
{

    /**
     * @var Cx_Banner
     */
    private $cx_Banner;

    public function __construct(Cx_Banner $cx_Banner)
    {
        $this->cx_Banner = $cx_Banner;
    }

    public function add($insertData)
    {
        return $this->cx_Banner->insertGetId($insertData);
    }

    public function index($offset, $limit)
    {
        return $this->initModel()
            ->with([
                'uploads' => function ($query){
                    $query->select(["image_id", "path"]);
                }
            ])
            ->orderByDesc('sort')
            ->offset($offset)
            ->limit($limit)
            ->addSelect(["id", "uploads_id","type", "location","url", "sort"])
            ->get();
    }

    public function count()
    {
        return $this->initModel()->count();
    }

    /**
     * @return Cx_Banner
     */
    public function initModel()
    {
        return $this->initQueryCondition($this->cx_Banner);
    }

    public function initQueryCondition($model)
    {
          $type = request()->get('type', 0);
          if ($type > 0) {
              $model = $model->where('type', $type);
          }
          $location = request()->get('location', 0);
          if ($location > 0) {
              $model = $model->where('location', $location);
          }
          return $model;
    }

    public function del($id)
    {
        return $this->cx_Banner->where('id', $id)->delete();
    }

    public function save($post)
    {
        return $this->cx_Banner->where('id', $post['id'])->update($post);
    }


}
