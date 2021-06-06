<?php


namespace App\Http\Controllers\Ag;


use App\Http\Requests\Ag\LoginRequest;
use App\Services\Ag\LoginService;
use Illuminate\Support\Facades\Validator;

class Login extends Base
{

    protected $LoginService;

    public function __construct
    (
        LoginService $loginService
    )
    {
        $this->LoginService = $loginService;
    }

    public function login(LoginRequest $loginRequest)
    {
        try{
            $this->LoginService->login($loginRequest->validated());
            return $this->AppServiceReturn($this->LoginService);
        }catch (\Exception $e){
            return $this->AppHostErr($e);
        }
    }

    public function logout()
    {
        try{
            $this->LoginService->logout();
            return $this->AppServiceReturn($this->LoginService);
        }catch (\Exception $e){
            return $this->AppHostErr($e);
        }
    }

}
