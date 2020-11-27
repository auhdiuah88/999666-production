<?php


namespace App\Services\Api;


use App\Common\Common;
use App\Repositories\Api\ActivityRepository;
use App\Repositories\Api\UserRepository;
use App\Services\Library\Auth;
use App\Services\Library\Netease\IM;
use App\Services\Library\Netease\SMS;
use App\Services\Library\Upload;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class BaseService
{
    public $_code = 414;
    public $_msg = "fails";
    public $_data = [];

}
