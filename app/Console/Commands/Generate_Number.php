<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Game\GameRepository;

class Generate_Number extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Generate_Number';
    protected $GameRepository;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成彩票期数';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(GameRepository $GameRepository)
    {
        parent::__construct();
        $this->GameRepository = $GameRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->GameRepository->Generate_Gold_Number(env('GOLD_TIME',60));
        $this->GameRepository->Generate_Silver_Number(env('SILVER_TIME',120));
        $this->GameRepository->Generate_Jewelry_Number(env('JEWELRY_TIME',180));
        $this->GameRepository->Generate_Other_Number(env('OTHER_TIME',300));
    }
}
