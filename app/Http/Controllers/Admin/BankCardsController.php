<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\BankCardsService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BankCardsController extends Controller
{

    protected $BankCardsService;

    public function __construct
    (
        BankCardsService $bankCardsService
    )
    {
        $this->BankCardsService = $bankCardsService;
    }

    public function lists()
    {
        try{
            $validator = Validator::make(request()->post(),
                [
                    'page' => ['gte:1', 'integer'],
                    'size' => ['gte:1', 'lte:10', 'integer']
                ]
            );
            if($validator->fails())
                return $this->AppReturn(401,$validator->errors()->first());
            $this->BankCardsService->lists();
            return $this->AppReturn(
                $this->BankCardsService->_code,
                $this->BankCardsService->_msg,
                $this->BankCardsService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function add()
    {
        try{
            $validator = Validator::make(request()->post(),
                [
                    'bank_name' => ['required', 'min:2', 'max:50'],
                    'bank_card_account' => ['required', 'min:2', 'max:50', 'alpha_num'],
                    'bank_card_holder' => ['required', 'min:2', 'max:50'],
                    'status' => ['required', 'integer', Rule::in(1,2)],
                ]
            );
            if($validator->fails())
                return $this->AppReturn(401,$validator->errors()->first());
            $this->BankCardsService->add();
            return $this->AppReturn(
                $this->BankCardsService->_code,
                $this->BankCardsService->_msg,
                $this->BankCardsService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function edit()
    {
        try{
            $validator = Validator::make(request()->post(),
                [
                    'id' => ['required', 'integer', 'gte:1'],
                    'bank_name' => ['required', 'min:2', 'max:50'],
                    'bank_card_account' => ['required', 'min:2', 'max:50', 'alpha_num'],
                    'bank_card_holder' => ['required', 'min:2', 'max:50'],
                    'status' => ['required', 'integer', Rule::in(1,2)],
                ]
            );
            if($validator->fails())
                return $this->AppReturn(401,$validator->errors()->first());
            $this->BankCardsService->edit();
            return $this->AppReturn(
                $this->BankCardsService->_code,
                $this->BankCardsService->_msg,
                $this->BankCardsService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function delete()
    {
        try{
            $validator = Validator::make(request()->post(),
                [
                    'id' => ['required', 'integer', 'gte:1'],
                ]
            );
            if($validator->fails())
                return $this->AppReturn(401,$validator->errors()->first());
            $this->BankCardsService->delete();
            return $this->AppReturn(
                $this->BankCardsService->_code,
                $this->BankCardsService->_msg,
                $this->BankCardsService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function bankList()
    {
        try{
            $this->BankCardsService->bankList();
            return $this->AppReturn(
                $this->BankCardsService->_code,
                $this->BankCardsService->_msg,
                $this->BankCardsService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function editBankCard()
    {
        try{
            $validator = Validator::make(request()->post(),
                [
                    'id' => ['required', 'integer', 'gte:1'],
                    'bank_type_id' => ['required'],
                    'ifsc_code' => ['required'],
                    'account_holder' => ['required'],
                    'phone' => ['required'],
                    'mail' => ['required'],
                    'bank_num' => ['required'],
                ]
            );
            if($validator->fails())
                return $this->AppReturn(401,$validator->errors()->first());
            $this->BankCardsService->editBankCard();
            return $this->AppReturn(
                $this->BankCardsService->_code,
                $this->BankCardsService->_msg,
                $this->BankCardsService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

}
