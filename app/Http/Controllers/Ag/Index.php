<?php


namespace App\Http\Controllers\Ag;


class Index extends Base
{

    public function index()
    {
        return view('ag.index',['idx'=>1]);
    }

}
