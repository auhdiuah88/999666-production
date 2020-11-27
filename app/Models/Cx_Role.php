<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Role extends Model
{
    protected $table = "role";

    protected $primaryKey = "id";

    public $timestamps = false;

    public function Admin()
    {
        return $this->belongsTo(Cx_Admin::class, "role_id", "id");
    }
}
