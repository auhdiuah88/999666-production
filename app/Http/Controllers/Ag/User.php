<?php


namespace App\Http\Controllers\Ag;


class User extends Base
{

    public function inviteIndex()
    {
        return view('ag.invite_index', ['idx'=>4]);
    }

}
