<?php
namespace App\Http\Controllers\Plat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Libs\Aes;
use App\Libs\Games\Icg\IcgLog;
use Illuminate\Support\Facades\Crypt;

class ICG extends Controller{
    private $Icg;

    public function __construct
    (
        IcgLog $Icg
    )
    {
        $this->Icg = $Icg;
    }

}
