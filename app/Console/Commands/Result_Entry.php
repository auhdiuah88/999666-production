<?php

namespace App\Console\Commands;

use App\Services\Game\GameService;
use Illuminate\Console\Command;

class Result_Entry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Result_Entry';
    protected $GameService;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每分钟执行结算消费队列';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(GameService $GameService)
    {
        parent::__construct();
        $this->GameService = $GameService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->GameService->Settlement_Queue();
    }
}
