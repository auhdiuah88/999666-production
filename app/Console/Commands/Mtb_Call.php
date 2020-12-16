<?php


namespace App\Console\Commands;


use App\Repositories\Admin\WithdrawalRepository;
use Illuminate\Console\Command;

class Mtb_Call extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $WithdrawalRepository;

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
    public function __construct(WithdrawalRepository $withdrawalRepository)
    {
        parent::__construct();
        $this->WithdrawalRepository = $withdrawalRepository;
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

        }
    }

}
