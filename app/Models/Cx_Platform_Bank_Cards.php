<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cx_Platform_Bank_Cards extends Model
{

    protected $guarded = [];

    protected $table = "platform_bank_cards";

    protected $primaryKey = "id";

    use SoftDeletes;

    protected $deleteTime = "deleted_at";

    protected $hidden = ['delete_at'];

    protected $dateFormat = 'U';

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

}
