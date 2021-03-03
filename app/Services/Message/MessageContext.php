<?php


namespace App\Services\Message;


class MessageContext
{

    private $strategyList = [];

    public function __construct
    (
        IndiaMessage $indiaMessage,
        VnMessage $vnMessage
    )
    {
        $this->strategyList = [
            'india' => $indiaMessage,
            'vn' => $vnMessage
        ];
    }

    public function getStrategy(string $strategy): MessageStrategy
    {
        if (!isset($this->strategyList[$strategy])) {
            return false;
        }
        return $this->strategyList[$strategy];
    }

}
