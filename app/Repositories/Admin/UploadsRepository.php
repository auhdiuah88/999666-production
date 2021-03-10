<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Uploads;
use App\Repositories\BaseRepository;

class UploadsRepository extends BaseRepository
{

    private $Cx_Uploads;

    public function __construct
    (
        Cx_Uploads $cx_Uploads
    )
    {
        $this->Cx_Uploads = $cx_Uploads;
    }

    public function getImage($id)
    {
        return $this->Cx_Uploads->where('image_id', '=', $id)->select(['image_id', 'path'])->first();
    }

}
