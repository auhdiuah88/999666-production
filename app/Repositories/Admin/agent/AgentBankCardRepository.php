<?php


namespace App\Repositories\Admin\agent;


use App\Models\Cx_User_Bank;

class AgentBankCardRepository
{

    protected $Cx_User_Bank;

    public function __construct(Cx_User_Bank $cx_User_Bank){
        $this->Cx_User_Bank = $cx_User_Bank;
    }

    public function getBackCardList($where, $account_holder, $size){
        $con = [
            "user_id" => ["in", $where]
        ];
        if($account_holder)
            $con["account_holder"] = ["=", $account_holder];
        return makeModel($con,$this->Cx_User_Bank)
            ->with([
                'user' => function($query){
                    $query->select(['id', 'phone']);
                }
            ])
            ->paginate($size);
    }

}
