<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Banks;
use App\Models\Cx_Platform_Bank_Cards;
use App\Models\Cx_User_Bank;

class BankCardsRepository
{

    protected $Cx_Platform_Bank_Cards, $Cx_Banks, $Cx_User_Bank;

    public function __construct
    (
        Cx_Platform_Bank_Cards $cx_Platform_Bank_Cards,
        Cx_Banks $cx_Banks,
        Cx_User_Bank $cx_User_Bank
    )
    {
        $this->Cx_Platform_Bank_Cards = $cx_Platform_Bank_Cards;
        $this->Cx_Banks = $cx_Banks;
        $this->Cx_User_Bank = $cx_User_Bank;
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

    public function bankList()
    {
        $county = env('COUNTRY','india');
        $where = [
            'status' => ['=', 1]
        ];
        $type = 0;
        switch($county){
            case 'india':
                $type = 1;
                break;
            case 'vn':
                $type = 2;
                break;
            case 'br':
                $type = 3;
                break;
        }
        $where['type'] = ['=', $type];
        return makeModel($where, $this->Cx_Banks)->select(['banks_id', 'bank_name'])->get();
    }

    public function editBankCard($id, $data)
    {
        return $this->Cx_User_Bank->where('id', '=', $id)->update($data);
    }

}
