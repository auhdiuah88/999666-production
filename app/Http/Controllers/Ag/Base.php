<?php


namespace App\Http\Controllers\Ag;


use App\Http\Controllers\Controller;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;

class Base extends Controller
{

    public function AppServiceReturn($Service)
    {
        return $this->AppReturn(
            $Service->_code,
            $Service->_msg,
            $Service->_data
        );
    }

    public function AppHostErr(\Exception $e)
    {
        Log::channel('apidebug')->info(request()->url(),['err'=>$e->getMessage(), 'file'=>$e->getFile(), 'line'=>$e->getLine()]);
        Log::channel('mytest')->info('test',['testststs']);
        return $this->AppReturn(
            405,
            'request time out'
        );
    }

    public function AppValidatorReturn(Validator $validator)
    {
        return $this->AppReturn(401,$validator->errors()->first());
    }

}
