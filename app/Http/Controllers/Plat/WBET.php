<?php
namespace App\Http\Controllers\Plat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Libs\Aes;
use App\Libs\Games\WBET\WbetLog;
use Illuminate\Support\Facades\Crypt;

class WBET extends Controller{
    private $Wbet;

    public function __construct
    (
        WbetLog $Wbet
    )
    {
        $this->Wbet = $Wbet;
    }



}
