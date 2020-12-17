<?php


namespace App\Console\Commands;


use App\Repositories\Admin\WithdrawalRepository;
use App\Services\Pay\MTBpay;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Mtb_Call extends Command
{

    protected $signature = 'Mtb_Call';

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
//        Log::channel('kidebug')->debug("task_test_2",["time"=>date("Y-m-d H:i:s")]);
        $list = $this->WithdrawalRepository->getMTBPayWaitCallList();
        if($list->isEmpty())return true;
        foreach($list as $item){
            $res = $this->MTBpay->callWithdrawBack($item);
            if(!$res || $res['status'] != 'UNKNOW'){
                $this->WithdrawalRepository->callMTBFail($item);
            }else{
                if($res['status'] != 'SUCCESS'){
                    $this->WithdrawalRepository->callMTBSuccess($item);
                }else{
                    $this->WithdrawalRepository->callMTBDefeat($item);
                }
            }
        }
        Log::channel('kidebug')->debug("task_mtb_call",["list"=>$list->toArray()]);
    }

}
