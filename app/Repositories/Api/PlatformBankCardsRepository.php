<?php


namespace App\Repositories\Api;


use App\Models\Cx_Platform_Bank_Cards;

class PlatformBankCardsRepository
{

    protected $Cx_Platform_Bank_Cards;

    public function __construct
    (
        Cx_Platform_Bank_Cards $cx_Platform_Bank_Cards
    )
    {
        $this->Cx_Platform_Bank_Cards = $cx_Platform_Bank_Cards;
    }

    public function lists()
    {
        return $this->Cx_Platform_Bank_Cards->where("status", "=", 1)->select(['id', 'bank_name', 'bank_card_account', 'bank_card_holder'])->get();
    }

}
