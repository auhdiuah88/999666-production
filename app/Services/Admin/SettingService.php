<?php


namespace App\Services\Admin;


use App\Repositories\Admin\SettingRepository;
use App\Services\BaseService;

class SettingService extends BaseService
{

    private $SettingRepository;

    public function __construct
    (
        SettingRepository $settingRepository
    )
    {
        $this->SettingRepository = $settingRepository;
    }

    /**
     * get staff role id
     * @return mixed
     */
    public function getStaffId(){
        $staff_id = $this->SettingRepository->getStaff();
        $this->_data = $staff_id;
        return true;
    }

    public function editStaffId(){
//        $role_id =
    }

}
