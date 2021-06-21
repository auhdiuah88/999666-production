<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cx_System_Tips extends Model
{

    protected $table = "system_tips";

    protected $primaryKey = "id";

    protected $dateFormat = "U";

    use SoftDeletes;

    protected $guarded = [];

    public const UPDATED_AT = "update_time";
    public const CREATED_AT = "create_time";

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

}
