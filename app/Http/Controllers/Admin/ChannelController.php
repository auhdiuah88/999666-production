<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\ChannelService;

class ChannelController extends Controller
{

    private $ChannelService;

    public function __construct
    (
        ChannelService $channelService
    )
    {
        $this->ChannelService = $channelService;
    }

    public function statistics()
    {
        try{
            $this->ChannelService->statistics();
            return $this->AppReturn(
                $this->ChannelService->_code,
                $this->ChannelService->_msg,
                $this->ChannelService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr',$e);
            return $this->AppReturn(402,$e->getMessage());
        }
    }

}
