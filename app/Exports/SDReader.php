<?php


namespace App\Exports;


use Maatwebsite\Excel\Concerns\ToArray;

class SDReader implements ToArray
{

    public function array(array $array)
    {
        return $array;
    }
}
