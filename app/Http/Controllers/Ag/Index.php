<?php


namespace App\Http\Controllers\Ag;


class Index extends Base
{

    public function index()
    {
        return view('ag.index',['idx'=>1]);
    }

    public function mIndex()
    {
        return view('ag.m.index', ['title'=>trans('ag.agent_center')]);
    }

    public function mDesc()
    {
        return view('ag.m.desc', ['title'=>trans('ag.agent_desc'), 'prev'=>1]);
    }

}
