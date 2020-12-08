<?php


namespace App\Services\Api;


use App\Common\Common;
use App\Services\Library\Auth;
use App\Services\Library\Netease\IM;
use App\Services\Library\Netease\SMS;
use App\Services\Library\Upload;

class BaseService
{
    public $_code = 414;
    public $_msg = "fails";
    public $_data = [];

}
