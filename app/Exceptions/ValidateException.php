<?php


namespace App\Exceptions;


class ValidateException extends \Exception
{
    public $message = '';

    public function __construct($params)
    {
        $this->message = $params;
    }

}
