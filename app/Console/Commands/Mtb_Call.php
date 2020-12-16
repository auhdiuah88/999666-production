<?php


namespace App\Console\Commands;


use App\Repositories\Admin\WithdrawalRepository;
use App\Services\Pay\MTBpay;
use Illuminate\Console\Command;

class Mtb_Call extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $WithdrawalRepository, $MTBpay;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '请求MTBpay获取代付结果';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(WithdrawalRepository $withdrawalRepository, MTBpay $mTBpay)
    {
        parent::__construct();
        $this->WithdrawalRepository = $withdrawalRepository;
        $this->MTBpay = $mTBpay;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $list = $this->WithdrawalRepository->getMTBPayWaitCallList();
        if($list->isEmpty())return true;
        foreach($list as $item){
            $res = $this->MTBpay->callWithdrawBack($item);
            if(!$res){
                $this->WithdrawalRepository->callMTBFail($item->call_count);
            }else{
                if($res['status'] != 'SUCCESS'){

                }else{

                }
            }
        }
    }

}
