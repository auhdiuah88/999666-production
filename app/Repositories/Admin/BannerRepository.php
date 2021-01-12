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

    public function index($get)
    {
        return $this->cx_Banner
            ->with([
                'uploads' => function ($query){
                    $query->select(["image_id", "path"]);
                }
            ])
            ->addSelect(["id", "uploads_id","type", "location"])->get()->toArray();
    }

    public function count($get)
    {
        return $this->cx_Banner->count();
    }
}
