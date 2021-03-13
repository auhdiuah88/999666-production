<?php


namespace App\Http\Middleware;


use Closure;

class ParamsDecryptMiddleware
{

    protected $msg = '';
    protected $params = [];
    protected $code = 0;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $i_c = env('IS_CRYPT',false);
        $params = $request->input('p');
        if(!$this->checkData($params, $i_c))
        {
            $rtn = [
                "code" => 403,
                "msg" => $this->msg
            ];
            if($i_c && $this->code != 101){
                $rtn = aesEncrypt(json_encode($rtn));
            }
            return response()->json($rtn);
        }
        foreach($this->params as $key => $value)
        {
            $request->offsetSet($key,$value);
        }
        $request->offsetUnset('p');
        $request->offsetUnset('t');
        $response = $next($request);
        if($i_c){
            $data = $response->getContent()?? "";
            $response->setContent(aesEncrypt($data));
        }
        return $response;
    }

    public function checkData($params, $i_c)
    {
        if(!$params)
        {
            $this->msg = 'params wrong';
            return false;
        }
        if($i_c){
            $data = aesDecrypt($params);
            if(!$data)
            {
                $this->msg = 'Please refresh the page and try again.';
                $this->code = 101;
                return false;
            }
        }else{
            $data = $params;
        }
        if(!is_array($data))
            $data = json_decode($data,true);
        if(!isset($data["t"]))
        {
            $this->msg = 'miss param';
            return false;
        }
        if($data['t'] < time()-10)
        {
            $this->msg = 'bad request';
            return false;
        }
        $this->params = $data;
        return true;
    }

}
