<?php


namespace App\Services\Ag;


use App\Repositories\Ag\ReportRepository;
use App\Repositories\Ag\UserRepository;
use App\Services\BaseService;

class ReportsService extends BaseService
{

    protected $ReportRepository, $UserRepository;

    public function __construct
    (
        ReportRepository $reportRepository,
        UserRepository $userRepository
    )
    {
        $this->ReportRepository = $reportRepository;
        $this->UserRepository = $userRepository;
    }

    public function getAgReport()
    {
        $phone = trim(request()->input('phone',''));
        if($phone){
            ##获取用户
            $user = $this->UserRepository->getMemberByPhone($phone);
            if($user)
            {
                $this->ReportRepository->user_ids = $this->UserRepository->getMemberUserIds($user->id);
            }else{
                $this->ReportRepository->user_ids = [];
            }
        }else{
            $this->ReportRepository->user_ids =  $this->UserRepository->getMemberUserIds();;
        }
        ##时间
        $time_flag = request()->input('time_flag',1);
        switch ($time_flag){
            case 1:
                $timestamp = [day_start(), day_end()];
                break;
            case 2:
                $timestamp = [last_day_start(), last_day_end()];
                break;
            case 3:
                $timestamp = [month_start(), month_end()];
                break;
            case 4:
                $timestamp = [last_month_start(), last_month_end()];
                break;
            default:
                $timestamp = [day_start(), day_end()];
                break;
        }
        $this->ReportRepository->timestamp = $timestamp;
        $this->_data = $this->ReportRepository->getAgReport();
    }

}
