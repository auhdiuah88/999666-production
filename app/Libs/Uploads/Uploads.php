<?php


namespace App\Libs\Uploads;

use App\Dictionary\UploadDic;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use function Sodium\compare;

class Uploads
{

    /**
     * @var UploadedFile
     */
    protected $file; //文件

    protected $key = "image"; //传递的键名

    protected $limitSize = null; //大小限制,null为不限制

    protected $limitExt = null;  //类型限制,null为不限制

    protected $fileName = null; //文件名,null为系统生成

    protected $ext;  //文件扩展名

    protected $cate = 0; //分类

    protected $error = "";

    public function __construct
    (
        $key=null,
        $fileName=null,
        $limitSize=null,
        $limitExt=null,
        $cate=0
    )
    {
        if($key)$this->key = $key;
        if($fileName)$this->fileName = $fileName;
        if($limitSize)$this->limitSize = $limitSize;
        if($limitExt)$this->limitExt = $limitExt;
        if($cate)$this->cate = $cate;
    }

    public function upload($key=null)
    {
        try{
            if($key)$this->key = $key;
            $this->file = request()->file($this->key);
            if(!$this->check()){
                return false;
            }
            $filePath = $this->file->storeAs('public/' . $this->getDir(),$this->getFileName());
            $path = 'storage/' . $this->getDir() . '/' . $this->getFileName();
            ##添加图片记录
            $id = DB::table('uploads')->insertGetId([
                'path' => $path,
                'file_path' => $filePath,
                'type' => UploadDic::getType($this->ext),
                'cate_id' => $this->cate,
                'created_at' => time()
            ]);
            return compact('id','path');
        }catch(\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }

    }

    protected function check():bool
    {
        ##验证文件
        if(!$this->file){
            $this->error = '上传文件不存在';
            return false;
        }
        ##验证大小
        if($this->limitSize && $this->file->getSize() > $this->limitSize){
            $this->error = '上传文件超出限制大小';
            return false;
        }
        $this->ext = $this->file->getClientOriginalExtension();
        ##验证扩展
        if($this->limitExt && !in_array($this->ext, $this->limitExt)){
            $this->error = '上传文件格式不支持';
            return false;
        }
        return true;
    }

    protected function getDir():string
    {
        return Config::$cate[$this->cate]['dir'] . '/' .date("Ymd");
    }

    protected function getFileName():string
    {
        if($this->fileName)return $this->fileName . '.' . $this->ext;
        return time() . rand(1111, 9999) . '.'. $this->ext;
    }

    public function getError():string
    {
        return $this->error;
    }

}
