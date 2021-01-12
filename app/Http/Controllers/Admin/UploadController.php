<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Libs\Uploads\Uploads;
use Illuminate\Support\Facades\Validator;

class UploadController extends Controller
{

    public function upload()
    {
        try{
            $validator = Validator::make(request()->post(), [
                'name' => ['required'],
                'file' => ['file']
            ]);
            if($validator->fails())
                return $this->AppReturn(403,$validator->errors()->first());
            $name = request()->post('name');
            $uploadEngine = new Uploads($name,null,5000000,['jpg', 'png', 'jpeg']);
            if(!$res = $uploadEngine->upload())
                return $this->AppReturn(403,$uploadEngine->getError());
            return $this->AppReturn(200,'upload success!',$res);
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

}
