<?php


namespace App\Services\Admin;


use App\Repositories\Admin\BankCardsRepository;
use App\Services\BaseService;

class BankCardsService extends BaseService
{

    protected $BankCardsRepository;

    public function __construct
    (
        BankCardsRepository $bankCardsRepository
    )
    {
        $this->BankCardsRepository = $bankCardsRepository;
    }

    public function lists()
    {
        $this->_data = $this->BankCardsRepository->lists([],$this->sizeInput());
    }

    public function add()
    {
        if($this->BankCardsRepository->add($this->getData()) === false){
            $this->_code = 401;
            $this->_msg = '银行卡添加失败';
        }
    }

    public function edit()
    {
        $id = $this->intInput('id');
        if($this->BankCardsRepository->edit(array_merge($this->getData(),compact('id'))) === false){
            $this->_code = 401;
            $this->_msg = '银行卡编辑失败';
        }
    }

    protected function getData(): array
    {
        $data = [
            'bank_name' => $this->strInput('bank_name'),
            'bank_card_account' => $this->strInput('bank_card_account'),
            'bank_card_holder' => $this->strInput('bank_card_holder'),
            'status' => $this->intInput('status',1),
        ];
        return $data;
    }

    public function update()
    {
        $data = [
            'field' => $this->strInput('field'),
            'value' => $this->strInput('value'),
            'id' => $this->intInput('id')
        ];
        if($this->BankCardsRepository->edit($data) === false){
            $this->_code = 401;
            $this->_msg = '操作失败';
        }
    }

    public function delete()
    {
        if($this->BankCardsRepository->delete($this->intInput('id')) === false){
            $this->_code = 401;
            $this->_msg = '删除失败';
        }
    }

    public function bankList()
    {
        $this->_data = $this->BankCardsRepository->bankList();
    }

    public function editBankCard()
    {
        $data = [
            'bank_num' => $this->strInput('bank_num'),
            'mail' => $this->strInput('mail'),
            'bank_type_id' => $this->strInput('bank_type_id'),
            'phone' => $this->strInput('phone'),
            'account_holder' => $this->strInput('account_holder'),
            'ifsc_code' => $this->strInput('ifsc_code'),
        ];
        $id = $this->intInput('id');
        $res = $this->BankCardsRepository->editBankCard($id, $data);
        if($res === false){
            $this->_code = 401;
            $this->_msg = '操作失败';
        }
        $this->_msg = '操作成功';
    }

}
