<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Platform_Bank_Cards;

class BankCardsRepository
{

    protected $Cx_Platform_Bank_Cards;

    public function __construct
    (
        Cx_Platform_Bank_Cards $cx_Platform_Bank_Cards
    )
    {
        $this->Cx_Platform_Bank_Cards = $cx_Platform_Bank_Cards;
    }

    public function add($data)
    {
        return $this->Cx_Platform_Bank_Cards->create($data);
    }

    public function lists($where, $size)
    {
        return makeModel($where, $this->Cx_Platform_Bank_Cards)
            ->paginate($size);
    }

    public function update($data)
    {
        return $this->Cx_Platform_Bank_Cards->where("id", "=", $data['id'])->update([$data['filed']=>$data['value']]);
    }

    public function edit($data)
    {
        return $this->Cx_Platform_Bank_Cards->where("id", "=", $data['id'])->update($data);
    }

    public function delete($id)
    {
        return $this->Cx_Platform_Bank_Cards->destroy($id);
    }

}
