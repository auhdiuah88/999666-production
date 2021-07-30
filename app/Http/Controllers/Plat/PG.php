<?php
namespace App\Http\Controllers\Plat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Libs\Aes;
use App\Libs\Games\Pg\PgLog;
use Illuminate\Support\Facades\Crypt;

class PG extends Controller{
    private $Pg;

    public function __construct
    (
        PgLog $Pg
    )
    {
        $this->Pg = $Pg;
    }

    //PG令牌验证
    public function VerifySession(){

    }

}
