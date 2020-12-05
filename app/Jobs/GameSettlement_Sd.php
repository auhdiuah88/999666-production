<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Game\SscService;
use App\Services\Game\Ssc_TwoService;
use App\Services\Game\Ssc_ThreeService;
use App\Services\Game\Ssc_FourService;

class GameSettlement_Sd implements ShouldQueue
{
    protected $game_id;
    protected $id;
    protected $prize_number;
    protected $SscService;
    protected $Ssc_TwoService;
    protected $Ssc_ThreeService;
    protected $Ssc_FourService;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id,$game_id,$prize_number)
    {
        $this->game_id=$game_id;
        $this->id=$id;
        $this->prize_number=$prize_number;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SscService $SscService,Ssc_TwoService $Ssc_TwoService,Ssc_ThreeService $Ssc_ThreeService,Ssc_FourService $Ssc_FourService)
    {
        $this->SscService=$SscService;
        $this->Ssc_TwoService=$Ssc_TwoService;
        $this->Ssc_ThreeService=$Ssc_ThreeService;
        $this->Ssc_FourService=$Ssc_FourService;

        if($this->game_id==1){
            $this->SscService->ssc_sd($this->id, $this->prize_number);
        }else if($this->game_id==2){
            $this->Ssc_TwoService->ssc_sd($this->id, $this->prize_number);
        }else if($this->game_id==3){
            $this->Ssc_ThreeService->ssc_sd($this->id, $this->prize_number);
        }else if($this->game_id==4){
            $this->Ssc_FourService->ssc_sd($this->id, $this->prize_number);
        }
    }
}
