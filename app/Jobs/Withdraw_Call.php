<?php

namespace App\Jobs;

use App\Repositories\Admin\WithdrawalRepository;
use App\Services\Pay\PayContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Withdraw_Call implements ShouldQueue
{

    protected $id, $WithdrawalRepository, $PayContext;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WithdrawalRepository $withdrawalRepository, PayContext $payContext)
    {
        $this->WithdrawalRepository = $withdrawalRepository;
        $this->PayContext = $payContext;
        $withdrawLog = $this->WithdrawalRepository->findById($this->id);
        if($withdrawLog->status != 1)return;
        $payProvide = $withdrawLog->with_type;
        $strategyClass = $this->PayContext->getStrategy($payProvide);  // 获取支付公司类
        if(!$strategyClass){
            $withdrawLog->message = '没有相关支付类';
            $withdrawLog->save();
        }
        $result = $strategyClass->withdrawalOrder($withdrawLog);
        if (!$result) {
            $withdrawLog->message = $strategyClass->_msg;
            $withdrawLog->pay_status = 3;
            $withdrawLog->save();
            return;
        }
        if(isset($result['pltf_order_no'])){
            $withdrawLog->pltf_order_no = $result['pltf_order_no'];
            $withdrawLog->save();
        }
    }
}
