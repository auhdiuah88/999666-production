<?php


namespace App\Http\Controllers\Plat;


use App\Http\Controllers\Controller;
use App\Libs\Games\WDYY\Client;

class BL extends Controller
{

    protected $Client;

    public function __construct
    (
        Client $client
    )
    {
        $this->Client = $client;
    }

    public function balance()
    {

    }

}
