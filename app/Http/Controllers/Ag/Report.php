<?php


namespace App\Http\Controllers\Ag;


class Report extends Base
{

    public function index()
    {
        return view('ag.report', ['idx'=>2]);
    }

}
