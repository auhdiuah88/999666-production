<?php


namespace App\Services\Admin;


use App\Repositories\Admin\ChannelRepository;
use App\Services\BaseService;

class ChannelService extends BaseService
{

    private $ChannelRepository;

    public function __construct
    (
        ChannelRepository $channelRepository
    )
    {
        $this->ChannelRepository = $channelRepository;
    }

    public function statistics():bool
    {
        ##获取通道列表
        $channels = channels();
        if(empty($channels)){
            $this->_msg = '未开通第三方支付通道';
            $this->_code = 401;
            return false;
        }
        $timeMap = [];
        $start_time = $this->intInput('start_time');
        $end_time = $this->strInput('end_time');
        if($start_time && $end_time){
            $timeMap = [$start_time, $end_time];
        }
        $this->_data = $this->ChannelRepository->statistics($channels, $timeMap);
        return true;
    }

}
