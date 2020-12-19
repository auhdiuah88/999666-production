<?php


namespace App\Repositories\Admin\agent;


use App\Models\Cx_User_Bank;

class AgentBankCardRepository
{

    protected $Cx_User_Bank;

    public function __construct(Cx_User_Bank $cx_User_Bank){
        $this->Cx_User_Bank = $cx_User_Bank;
    }

    public function getBackCardList($where, $size){
        return $this->Cx_User_Bank
            ->with([
                'user' => function($query){
                    $query->select(['id', 'phone as phone_hide']);
                }
            ])
            ->whereIn("user_id",$where)
            ->paginate($size);
    }

}
