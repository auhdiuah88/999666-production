<?php

namespace App\Console\Commands;

use App\Repositories\Api\UserRepository;
use Illuminate\Console\Command;

class CheckNewOrOld extends Command
{
    private $repository, $timestamp;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CheckNewOrOld';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CheckNewOrOld';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();
        $this->repository = $userRepository;
        $this->timestamp = strtotime('-5 days');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = $this->repository->findNewUser();
        foreach ($users as $user) {
            if ($this->timestamp > $user["reg_time"]) {
                $this->repository->updateNewOrOld($user["id"]);
            }
        }
    }
}
